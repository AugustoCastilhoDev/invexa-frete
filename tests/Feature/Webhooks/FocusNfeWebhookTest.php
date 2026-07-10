<?php

namespace Tests\Feature\Webhooks;

use App\Models\Documento;
use App\Models\EmissaoFiscal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FocusNfeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_atualiza_emissao_e_cria_documento_ao_autorizar(): void
    {
        $emissao = EmissaoFiscal::factory()->create(['referencia' => 'ref-123', 'tipo' => 'cte']);

        $response = $this->postJson(route('webhooks.focus-nfe'), [
            'ref' => 'ref-123',
            'status' => 'autorizado',
            'chave_nfe' => str_repeat('9', 44),
        ]);

        $response->assertNoContent();
        $emissao->refresh();
        $this->assertSame('autorizado', $emissao->status);
        $this->assertNotNull($emissao->documento_id);
        $this->assertSame(1, Documento::count());
    }

    public function test_rejeita_requisicao_com_token_invalido(): void
    {
        config(['services.focus_nfe.webhook_token' => 'token-correto']);

        $response = $this->postJson(route('webhooks.focus-nfe') . '?token=token-errado', [
            'ref' => 'ref-qualquer',
            'status' => 'autorizado',
        ]);

        $response->assertForbidden();
    }

    public function test_aceita_requisicao_com_token_correto(): void
    {
        config(['services.focus_nfe.webhook_token' => 'token-correto']);
        $emissao = EmissaoFiscal::factory()->create(['referencia' => 'ref-token']);

        $response = $this->postJson(route('webhooks.focus-nfe') . '?token=token-correto', [
            'ref' => 'ref-token',
            'status' => 'rejeitado',
        ]);

        $response->assertNoContent();
        $this->assertSame('rejeitado', $emissao->fresh()->status);
    }

    public function test_ignora_referencia_desconhecida_sem_erro(): void
    {
        $response = $this->postJson(route('webhooks.focus-nfe'), [
            'ref' => 'ref-inexistente',
            'status' => 'autorizado',
        ]);

        $response->assertNoContent();
    }

    public function test_ignora_payload_sem_referencia_sem_erro(): void
    {
        $response = $this->postJson(route('webhooks.focus-nfe'), [
            'status' => 'autorizado',
        ]);

        $response->assertNoContent();
    }
}
