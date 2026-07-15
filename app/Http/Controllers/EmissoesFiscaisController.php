<?php

namespace App\Http\Controllers;

use App\Models\EmissaoFiscal;
use App\Models\Veiculo;
use App\Models\Viagem;
use App\Services\FocusNfe\FocusNfeClient;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmissoesFiscaisController extends Controller
{
    public function store(Request $request, Viagem $viagem, string $tipo, FocusNfeClient $focusNfe)
    {
        abort_unless(in_array($tipo, ['cte', 'mdfe'], true), 404);

        $empresa = $viagem->empresa;

        abort_unless($empresa->focus_nfe_ativo, 403, 'Emissão fiscal não está habilitada para esta empresa.');

        $emissao = EmissaoFiscal::create([
            'viagem_id'  => $viagem->id,
            'tipo'       => $tipo,
            'referencia' => "invexa-{$viagem->id}-{$tipo}-" . now()->timestamp,
            'status'     => 'processando_autorizacao',
        ]);

        $payload = $tipo === 'cte' ? $this->montarPayloadCte($viagem) : $this->montarPayloadMdfe($viagem);
        $emissao->update(['payload_enviado' => $payload]);

        $resposta = $tipo === 'cte'
            ? $focusNfe->emitirCte($empresa, $emissao->referencia, $payload)
            : $focusNfe->emitirMdfe($empresa, $emissao->referencia, $payload);

        if (! $resposta) {
            $emissao->update([
                'status'        => 'erro_autorizacao',
                'mensagem_erro' => 'Não foi possível iniciar a emissão — veja os logs da aplicação.',
            ]);

            return redirect()->route('viagens.show', $viagem)
                ->with('error', 'Não foi possível iniciar a emissão do ' . $emissao->tipo_formatado . '.');
        }

        $emissao->aplicarRespostaFocus($resposta);

        return redirect()->route('viagens.show', $viagem)
            ->with('success', $emissao->tipo_formatado . ' enviado para autorização.');
    }

    public function atualizarStatus(EmissaoFiscal $emissaoFiscal, FocusNfeClient $focusNfe)
    {
        $resposta = $emissaoFiscal->tipo === 'cte'
            ? $focusNfe->consultarCte($emissaoFiscal->empresa, $emissaoFiscal->referencia)
            : $focusNfe->consultarMdfe($emissaoFiscal->empresa, $emissaoFiscal->referencia);

        if (! $resposta) {
            return back()->with('error', 'Não foi possível consultar o status agora.');
        }

        $emissaoFiscal->aplicarRespostaFocus($resposta);

        return back()->with('success', 'Status atualizado.');
    }

    public function encerrar(Request $request, EmissaoFiscal $emissaoFiscal, FocusNfeClient $focusNfe)
    {
        abort_unless($emissaoFiscal->podeEncerrar(), 422, 'Este MDF-e não pode ser encerrado agora.');

        $dados = $request->validate([
            'data' => 'required|date',
            'sigla_uf' => 'required|string|size:2',
            'nome_municipio' => 'required|string|max:255',
        ]);

        $resposta = $focusNfe->encerrarMdfe($emissaoFiscal->empresa, $emissaoFiscal->referencia, $dados);

        if (! $resposta) {
            return back()->with('error', 'Não foi possível encerrar o MDF-e agora — veja os logs da aplicação.');
        }

        $emissaoFiscal->aplicarEncerramento($resposta);

        return back()->with(
            $emissaoFiscal->status === 'encerrado' ? 'success' : 'error',
            $emissaoFiscal->status === 'encerrado'
                ? 'MDF-e encerrado com sucesso.'
                : ('Falha ao encerrar: ' . $emissaoFiscal->mensagem_erro)
        );
    }

    public function index(Request $request)
    {
        $query = $this->emissoesFiltradas($request);

        $totalRegistros = (clone $query)->count();

        $emissoes = $query->orderByDesc('created_at')
            ->paginate(15)->withQueryString();

        $veiculos = Veiculo::orderBy('placa')->get();

        return view('emissoes-fiscais.index', compact('emissoes', 'veiculos', 'totalRegistros'));
    }

    public function csv(Request $request): StreamedResponse
    {
        $emissoes = $this->emissoesFiltradas($request)->orderByDesc('created_at')->get();

        return response()->streamDownload(function () use ($emissoes) {
            $saida = fopen('php://output', 'w');
            fwrite($saida, "\xEF\xBB\xBF");

            fputcsv($saida, [
                'Viagem', 'Veículo', 'Motorista', 'Tipo', 'Número', 'Série',
                'Status', 'Emitido em', 'Encerrado em',
            ], ';');

            foreach ($emissoes as $emissao) {
                fputcsv($saida, [
                    $emissao->viagem_id,
                    $emissao->viagem?->veiculo?->placa,
                    $emissao->viagem?->motorista?->nome,
                    $emissao->tipo_formatado,
                    $emissao->numero,
                    $emissao->serie,
                    ucfirst(str_replace('_', ' ', $emissao->status)),
                    $emissao->created_at->format('d/m/Y H:i'),
                    optional($emissao->encerrado_em)->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($saida);
        }, 'emissoes-fiscais.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function emissoesFiltradas(Request $request)
    {
        return EmissaoFiscal::with(['viagem.veiculo', 'viagem.motorista'])
            ->when($request->input('tipo'), fn ($q, $v) => $q->where('tipo', $v))
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->input('veiculo_id'), fn ($q, $v) => $q->whereHas('viagem', fn ($qq) => $qq->where('veiculo_id', $v)))
            ->when($request->input('data_inicio'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->input('data_fim'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
    }

    /**
     * Payload plano do CT-e (uma chave por campo, com sufixo _emitente/
     * _remetente/_destinatario), conforme doc.focusnfe.com.br/reference/emitir_cte
     * — a Focus NÃO usa objetos aninhados aqui. Remetente = a própria
     * transportadora (simplificação: hoje não há conceito de "quem despachou
     * a carga" separado de Empresa/Cliente no sistema).
     */
    private function montarPayloadCte(Viagem $viagem): array
    {
        $empresa = $viagem->empresa;
        $cliente = $viagem->cliente;

        return [
            'cfop' => $empresa->cfop_padrao,
            'natureza_operacao' => 'Prestação de serviço de transporte',
            'data_emissao' => now()->toIso8601String(),
            'codigo_municipio_envio' => $empresa->codigo_municipio,
            'municipio_envio' => $empresa->municipio,
            'uf_envio' => $empresa->uf,
            'codigo_municipio_inicio' => $viagem->origem_codigo_municipio,
            'municipio_inicio' => $viagem->origem,
            'uf_inicio' => $viagem->origem_uf,
            'codigo_municipio_fim' => $viagem->destino_codigo_municipio,
            'municipio_fim' => $viagem->destino,
            'uf_fim' => $viagem->destino_uf,
            'cnpj_emitente' => $empresa->cnpj,
            'inscricao_estadual_emitente' => $empresa->inscricao_estadual,
            'nome_emitente' => $empresa->nome,
            'logradouro_emitente' => $empresa->logradouro,
            'numero_emitente' => $empresa->numero,
            'bairro_emitente' => $empresa->bairro,
            'municipio_emitente' => $empresa->municipio,
            'cep_emitente' => $empresa->cep,
            'uf_emitente' => $empresa->uf,
            'telefone_emitente' => $empresa->telefone,
            'cnpj_remetente' => $empresa->cnpj,
            'inscricao_estadual_remetente' => $empresa->inscricao_estadual,
            'nome_remetente' => $empresa->nome,
            'logradouro_remetente' => $empresa->logradouro,
            'numero_remetente' => $empresa->numero,
            'bairro_remetente' => $empresa->bairro,
            'codigo_municipio_remetente' => $empresa->codigo_municipio,
            'municipio_remetente' => $empresa->municipio,
            'cep_remetente' => $empresa->cep,
            'uf_remetente' => $empresa->uf,
            'cnpj_destinatario' => $cliente?->cpf_cnpj,
            'inscricao_estadual_destinatario' => $cliente?->ie,
            'nome_destinatario' => $cliente?->nome,
            'telefone_destinatario' => $cliente?->telefone,
            'logradouro_destinatario' => $cliente?->logradouro,
            'numero_destinatario' => $cliente?->numero,
            'complemento_destinatario' => $cliente?->complemento,
            'bairro_destinatario' => $cliente?->bairro,
            'municipio_destinatario' => $cliente?->cidade,
            'cep_destinatario' => $cliente?->cep,
            'uf_destinatario' => $cliente?->estado,
            'valor_total_carga' => (float) $viagem->valor_frete,
            'produto_predominante' => $viagem->descricao_carga,
            'valor_total' => (float) $viagem->valor_frete,
            'valor_receber' => (float) $viagem->valor_frete,
            'icms_situacao_tributaria' => $empresa->icms_situacao_tributaria,
            'icms_aliquota' => $empresa->icms_aliquota,
            'modal' => '01',
            'modal_rodoviario' => ['rntrc' => $empresa->rntrc],
        ];
    }

    /**
     * Payload do MDF-e conforme campos.focusnfe.com.br/mdfe/*: condutores
     * (nome+CPF), municípios de carregamento (nome+código IBGE), percurso
     * (UFs) e documentos vinculados (chave do CT-e já autorizado da mesma
     * viagem, quando existir).
     */
    private function montarPayloadMdfe(Viagem $viagem): array
    {
        $empresa = $viagem->empresa;
        $veiculo = $viagem->veiculo;
        $motorista = $viagem->motorista;

        $cteAutorizado = $viagem->emissoesFiscais
            ->first(fn ($e) => $e->tipo === 'cte' && $e->status === 'autorizado');

        return [
            'data_emissao' => now()->toIso8601String(),
            'cnpj_emitente' => $empresa->cnpj,
            'municipios_carregamento' => [
                ['codigo' => $viagem->origem_codigo_municipio, 'nome' => $viagem->origem],
            ],
            'percursos' => $viagem->destino_uf && $viagem->destino_uf !== $viagem->origem_uf
                ? [['uf_percurso' => $viagem->origem_uf], ['uf_percurso' => $viagem->destino_uf]]
                : [['uf_percurso' => $viagem->origem_uf]],
            'placa_veiculo' => $veiculo?->placa,
            'renavam_veiculo' => $veiculo?->renavam,
            'tara_veiculo' => $veiculo?->tara_kg,
            'capacidade_kg_veiculo' => $veiculo?->capacidade_kg,
            'registro_nacional_transporte' => $empresa->rntrc,
            'condutores' => $motorista ? [[
                'nome' => $motorista->nome,
                'cpf' => preg_replace('/\D/', '', (string) $motorista->cpf),
            ]] : [],
            'conhecimentos_transporte' => $cteAutorizado
                ? [['chave_cte' => $cteAutorizado->chave_acesso]]
                : [],
        ];
    }
}
