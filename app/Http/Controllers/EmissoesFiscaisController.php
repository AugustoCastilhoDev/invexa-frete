<?php

namespace App\Http\Controllers;

use App\Models\EmissaoFiscal;
use App\Models\Viagem;
use App\Services\FocusNfe\FocusNfeClient;
use Illuminate\Http\Request;

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
