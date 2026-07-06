<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AsaasWebhookController extends Controller
{
    /**
     * Recebe eventos de pagamento do Asaas e só registra o status da
     * assinatura na empresa — não suspende nada automaticamente. A decisão
     * de desativar por inadimplência continua manual, pelo super admin.
     */
    public function __invoke(Request $request)
    {
        $tokenEsperado = config('services.asaas.webhook_token');

        if ($tokenEsperado && $request->header('asaas-access-token') !== $tokenEsperado) {
            abort(403);
        }

        $evento = $request->input('event');
        $subscriptionId = $request->input('payment.subscription');

        if (! $subscriptionId) {
            return response()->noContent();
        }

        $empresa = Empresa::where('asaas_subscription_id', $subscriptionId)->first();

        if (! $empresa) {
            Log::warning("Asaas webhook: assinatura {$subscriptionId} não encontrada em nenhuma empresa.");

            return response()->noContent();
        }

        $empresa->update([
            'asaas_status' => $evento,
            'asaas_last_event_at' => now(),
        ]);

        Log::info("Asaas webhook: evento {$evento} recebido para a empresa #{$empresa->id} ({$empresa->nome}).");

        return response()->noContent();
    }
}
