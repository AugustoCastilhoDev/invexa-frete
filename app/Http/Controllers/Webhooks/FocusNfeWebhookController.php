<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\EmissaoFiscal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FocusNfeWebhookController extends Controller
{
    /**
     * Recebe atualizações de status de CT-e/MDF-e da Focus NFe e só sincroniza
     * o registro correspondente — nunca desativa a empresa nem toma nenhuma
     * ação automática por causa de um erro/rejeição, mesma lógica já usada em
     * AsaasWebhookController. A Focus não assina os webhooks, então a
     * autenticação aqui é um segredo compartilhado via query string (embutido
     * na URL cadastrada no painel da Focus), não um header.
     *
     * O nome exato do campo que carrega nossa referência no payload da Focus
     * ainda não foi confirmado contra a doc/sandbox — usamos 'ref' como
     * melhor palpite e revisamos assim que houver uma chamada real.
     */
    public function __invoke(Request $request)
    {
        $tokenEsperado = config('services.focus_nfe.webhook_token');

        if ($tokenEsperado && $request->query('token') !== $tokenEsperado) {
            abort(403);
        }

        $referencia = $request->input('ref');

        if (! $referencia) {
            return response()->noContent();
        }

        $emissao = EmissaoFiscal::withoutGlobalScope('empresa')
            ->where('referencia', $referencia)
            ->first();

        if (! $emissao) {
            Log::warning("Focus NFe webhook: referência {$referencia} não encontrada em nenhuma emissão.");

            return response()->noContent();
        }

        $emissao->aplicarRespostaFocus($request->all());

        Log::info("Focus NFe webhook: status {$emissao->status} recebido para a emissão #{$emissao->id} (viagem #{$emissao->viagem_id}).");

        return response()->noContent();
    }
}
