<?php

namespace Tests\Feature\Viagens;

use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));
        $viagem->refresh();
        $this->assertNotNull($viagem->assinatura_motorista_path);
        $this->assertNotNull($viagem->assinatura_motorista_em);
        Storage::disk('public')->assertExists($viagem->assinatura_motorista_path);
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

    public function test_reassinar_substitui_a_assinatura_anterior_e_remove_o_arquivo_antigo(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'encerrada']);

        $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);
        $caminhoAntigo = $viagem->fresh()->assinatura_motorista_path;

        $this->patch(route('viagens.assinar', $viagem), ['assinatura' => $this->pngBase64()]);
        $caminhoNovo = $viagem->fresh()->assinatura_motorista_path;

        $this->assertNotEquals($caminhoAntigo, $caminhoNovo);
        Storage::disk('public')->assertMissing($caminhoAntigo);
        Storage::disk('public')->assertExists($caminhoNovo);
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
