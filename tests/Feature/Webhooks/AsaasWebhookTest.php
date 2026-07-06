<?php

namespace Tests\Feature\Webhooks;

use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsaasWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_atualiza_status_da_empresa_ao_receber_evento_valido(): void
    {
        $empresa = Empresa::factory()->create(['asaas_subscription_id' => 'sub_789']);

        $response = $this->postJson(route('webhooks.asaas'), [
            'event' => 'PAYMENT_RECEIVED',
            'payment' => ['subscription' => 'sub_789'],
        ]);

        $response->assertNoContent();
        $empresa->refresh();
        $this->assertSame('PAYMENT_RECEIVED', $empresa->asaas_status);
        $this->assertNotNull($empresa->asaas_last_event_at);
    }

    public function test_nao_suspende_empresa_automaticamente_em_atraso(): void
    {
        $empresa = Empresa::factory()->create(['asaas_subscription_id' => 'sub_atraso', 'status' => 'ativo']);

        $this->postJson(route('webhooks.asaas'), [
            'event' => 'PAYMENT_OVERDUE',
            'payment' => ['subscription' => 'sub_atraso'],
        ]);

        $empresa->refresh();
        $this->assertSame('PAYMENT_OVERDUE', $empresa->asaas_status);
        $this->assertSame('ativo', $empresa->status);
    }

    public function test_rejeita_requisicao_com_token_invalido(): void
    {
        config(['services.asaas.webhook_token' => 'token-correto']);

        $response = $this->postJson(route('webhooks.asaas'), [
            'event' => 'PAYMENT_RECEIVED',
            'payment' => ['subscription' => 'sub_qualquer'],
        ], ['asaas-access-token' => 'token-errado']);

        $response->assertForbidden();
    }

    public function test_aceita_requisicao_com_token_correto(): void
    {
        config(['services.asaas.webhook_token' => 'token-correto']);
        $empresa = Empresa::factory()->create(['asaas_subscription_id' => 'sub_token']);

        $response = $this->postJson(route('webhooks.asaas'), [
            'event' => 'PAYMENT_CONFIRMED',
            'payment' => ['subscription' => 'sub_token'],
        ], ['asaas-access-token' => 'token-correto']);

        $response->assertNoContent();
        $this->assertSame('PAYMENT_CONFIRMED', $empresa->fresh()->asaas_status);
    }

    public function test_ignora_evento_de_assinatura_desconhecida_sem_erro(): void
    {
        $response = $this->postJson(route('webhooks.asaas'), [
            'event' => 'PAYMENT_RECEIVED',
            'payment' => ['subscription' => 'sub_inexistente'],
        ]);

        $response->assertNoContent();
    }
}
