<?php

namespace Tests\Feature\Viagens;

use App\Models\Motorista;
use App\Models\User;
use App\Models\Viagem;
use App\Notifications\ComprovanteAcertoAssinadoNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssinaturaDigitalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PNG 1x1 válido, codificado em base64, para simular a assinatura vinda do canvas.
     */
    private function pngBase64(): string
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');

        return 'data:image/png;base64,' . base64_encode($png);
    }

    public function test_assina_viagem_aguardando_acerto(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'aguardando_acerto']);

        $response = $this->patch(route('viagens.assinar', $viagem), [
            'assinatura' => $this->pngBase64(),
        ], ['REMOTE_ADDR' => '203.0.113.10', 'HTTP_USER_AGENT' => 'TesteUA/1.0']);

        $response->assertRedirect(route('viagens.show', $viagem));
        $viagem->refresh();
        $this->assertNotNull($viagem->assinatura_motorista_path);
        $this->assertNotNull($viagem->assinatura_motorista_em);
        $this->assertEquals('203.0.113.10', $viagem->assinatura_motorista_ip);
        $this->assertEquals('TesteUA/1.0', $viagem->assinatura_motorista_user_agent);
        Storage::disk('public')->assertExists($viagem->assinatura_motorista_path);
    }

    public function test_nao_permite_assinar_de_novo_sem_reabrir_o_acerto(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'encerrada']);
        $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);

        $response = $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);

        $response->assertStatus(422);
    }

    public function test_admin_reabre_acerto_e_preserva_assinatura_anterior_no_historico(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $viagem = Viagem::factory()->create(['status' => 'encerrada']);
        $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);
        $caminhoAntigo = $viagem->fresh()->assinatura_motorista_path;
        $comprovanteAntigo = $viagem->fresh()->assinatura_motorista_comprovante_path;

        $response = $this->delete(route('viagens.assinatura.reabrir', $viagem));

        $response->assertRedirect(route('viagens.show', $viagem));
        $viagem->refresh();
        $this->assertNull($viagem->assinatura_motorista_path);
        $this->assertNull($viagem->assinatura_motorista_comprovante_path);
        $this->assertNull($viagem->assinatura_motorista_em);
        $this->assertNull($viagem->assinatura_motorista_ip);
        $this->assertNull($viagem->assinatura_motorista_user_agent);

        // arquivos antigos NÃO são apagados — ficam preservados como evidência
        Storage::disk('public')->assertExists($caminhoAntigo);
        Storage::disk('public')->assertExists($comprovanteAntigo);

        // e referenciados no histórico, junto de quem reabriu e quando
        $historico = $viagem->assinaturas_motorista_historico;
        $this->assertCount(1, $historico);
        $this->assertEquals($caminhoAntigo, $historico[0]['assinatura_path']);
        $this->assertEquals($comprovanteAntigo, $historico[0]['comprovante_path']);
        $this->assertEquals($admin->email, $historico[0]['reaberta_por']);

        // depois de reaberta, dá pra assinar de novo
        $response = $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);
        $response->assertRedirect(route('viagens.show', $viagem));
    }

    public function test_operador_nao_pode_reabrir_acerto(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'encerrada']);
        $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);

        $response = $this->delete(route('viagens.assinatura.reabrir', $viagem));

        $response->assertForbidden();
        $this->assertNotNull($viagem->fresh()->assinatura_motorista_em);
    }

    public function test_viagem_assinada_nao_pode_receber_novo_lancamento(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'encerrada']);
        $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);

        $response = $this->post(route('lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 100,
            'data_lancamento' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(422);
    }

    public function test_viagem_assinada_nao_pode_ser_editada(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'aguardando_acerto']);
        $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);

        $response = $this->put(route('viagens.update', $viagem), [
            'motorista_id'         => $viagem->motorista_id,
            'veiculo_id'           => $viagem->veiculo_id,
            'origem'               => $viagem->origem,
            'destino'              => $viagem->destino,
            'data_saida'           => $viagem->data_saida->format('Y-m-d'),
            'valor_frete'          => 9999,
            'percentual_motorista' => 10,
            'status'               => 'aguardando_acerto',
        ]);

        $response->assertStatus(422);
        $this->assertNotEquals(9999, $viagem->fresh()->valor_frete);
    }

    public function test_nao_permite_assinar_viagem_aberta(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'aberta']);

        $response = $this->patch(route('viagens.assinar', $viagem), [
            'assinatura' => $this->pngBase64(),
        ]);

        $response->assertStatus(400);
        $this->assertNull($viagem->fresh()->assinatura_motorista_path);
    }

    public function test_rejeita_payload_que_nao_e_uma_imagem_png_valida(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'encerrada']);

        $response = $this->patch(route('viagens.assinar', $viagem), [
            'assinatura' => 'data:image/png;base64,' . base64_encode('nao e um png de verdade'),
        ]);

        $response->assertSessionHasErrors('assinatura');
        $this->assertNull($viagem->fresh()->assinatura_motorista_path);
    }

    public function test_rejeita_payload_sem_o_prefixo_esperado(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'encerrada']);

        $response = $this->patch(route('viagens.assinar', $viagem), [
            'assinatura' => 'qualquer-coisa-que-nao-seja-um-data-uri',
        ]);

        $response->assertSessionHasErrors('assinatura');
        $this->assertNull($viagem->fresh()->assinatura_motorista_path);
    }

    public function test_assinatura_gera_e_guarda_comprovante_congelado(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'encerrada']);
        $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);

        $viagem->refresh();
        $this->assertNotNull($viagem->assinatura_motorista_comprovante_path);
        Storage::disk('public')->assertExists($viagem->assinatura_motorista_comprovante_path);
    }

    public function test_envia_comprovante_por_email_quando_motorista_tem_email(): void
    {
        Storage::fake('public');
        Notification::fake();
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create(['email' => 'motorista@example.com']);
        $viagem = Viagem::factory()->create(['status' => 'encerrada', 'motorista_id' => $motorista->id]);

        $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);

        Notification::assertSentOnDemand(
            ComprovanteAcertoAssinadoNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'motorista@example.com'
        );
    }

    public function test_nao_envia_email_quando_motorista_sem_email(): void
    {
        Storage::fake('public');
        Notification::fake();
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create(['email' => null]);
        $viagem = Viagem::factory()->create(['status' => 'encerrada', 'motorista_id' => $motorista->id]);

        $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);

        Notification::assertNothingSent();
    }

    public function test_pdf_com_assinatura_gera_documento_sem_erro(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'encerrada']);
        $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);

        $response = $this->get(route('viagens.imprimir', $viagem));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
