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

        $payload = $this->montarPayload($tipo, $viagem);
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
     * Monta as seções de nível raiz exigidas pela Focus NFe para CT-e/MDF-e a
     * partir dos dados já cadastrados na viagem. Isto é um scaffold da
     * estrutura, não o mapeamento campo-a-campo completo — a Focus exige
     * dezenas de campos por seção (endereço estruturado do remetente/
     * destinatário, dados do modal rodoviário, ICMS, etc.) que dependem de
     * cadastros (Cliente/Veiculo/Motorista) que hoje podem não ter todos os
     * dados necessários. Completar contra respostas reais de homologação
     * antes de usar em produção — não adivinhar os campos faltantes aqui.
     */
    private function montarPayload(string $tipo, Viagem $viagem): array
    {
        $empresa = $viagem->empresa;
        $cliente = $viagem->cliente;
        $veiculo = $viagem->veiculo;

        $base = [
            'natureza_operacao' => 'Prestação de serviço de transporte',
            'data_emissao' => now()->toIso8601String(),
            'cnpj_emitente' => $empresa->cnpj,
            'emitente' => [
                'cnpj' => $empresa->cnpj,
                'nome' => $empresa->nome,
                // TODO: inscrição estadual e endereço estruturado do emitente
                // ainda não existem no cadastro de Empresa — precisa ser
                // adicionado antes de emitir de verdade.
            ],
        ];

        if ($tipo === 'cte') {
            return array_merge($base, [
                'remetente' => [
                    'cnpj' => $empresa->cnpj,
                    'nome' => $empresa->nome,
                ],
                'destinatario' => [
                    'nome' => $cliente?->nome,
                    'cpf_cnpj' => $cliente?->cpf_cnpj,
                    // TODO: endereço estruturado do cliente
                ],
                'modal_rodoviario' => [
                    'placa' => $veiculo?->placa,
                ],
                'valores' => [
                    'valor_total' => (float) $viagem->valor_frete,
                    'valor_receber' => (float) $viagem->valor_frete,
                ],
                'operacao' => [
                    'cfop' => null, // TODO: definir CFOP correto por operação
                    'municipio_inicio' => $viagem->origem,
                    'municipio_fim' => $viagem->destino,
                ],
                'carga' => [
                    'produto_predominante' => null, // TODO
                ],
            ]);
        }

        return array_merge($base, [
            'veiculo_tracao' => [
                'placa' => $veiculo?->placa,
            ],
            'municipio_carregamento' => $viagem->origem,
            'percurso' => [$viagem->destino],
            // TODO: CT-es vinculados a este MDF-e, condutor (motorista), etc.
        ]);
    }
}
