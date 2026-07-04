<?php

namespace Tests\Feature\Viagens;

use App\Models\Documento;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentosTest extends TestCase
{
    use RefreshDatabase;

    public function test_adicionar_documento_com_chave_de_acesso(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();

        $response = $this->post(route('documentos.store', $viagem), [
            'tipo'          => 'cte',
            'numero'        => '123456',
            'chave_acesso'  => '35250000000000000000550010000000011000000017',
            'data_emissao'  => now()->format('Y-m-d'),
            'valor'         => 500,
            'status'        => 'pendente',
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertDatabaseHas('documentos', [
            'viagem_id'    => $viagem->id,
            'chave_acesso' => '35250000000000000000550010000000011000000017',
        ]);
    }

    public function test_adicionar_documento_sem_chave_de_acesso_funciona_normalmente(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();

        $response = $this->post(route('documentos.store', $viagem), [
            'tipo'          => 'cte',
            'numero'        => '123456',
            'data_emissao'  => now()->format('Y-m-d'),
            'valor'         => 500,
            'status'        => 'pendente',
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));
        $documento = Documento::firstOrFail();
        $this->assertNull($documento->chave_acesso);
        $this->assertNull($documento->url_consulta_sefaz);
    }

    public function test_editar_documento_para_adicionar_chave_de_acesso_depois(): void
    {
        $this->actingAs(User::factory()->create());
        $documento = Documento::factory()->create(['chave_acesso' => null, 'status' => 'pendente']);

        $response = $this->patch(route('documentos.update', $documento), [
            'status'       => $documento->status,
            'chave_acesso' => '35250000000000000000550010000000011000000017',
        ]);

        $response->assertRedirect(route('viagens.show', $documento->viagem));
        $this->assertEquals(
            '35250000000000000000550010000000011000000017',
            $documento->fresh()->chave_acesso
        );
    }

    public function test_editar_documento_nao_exige_reenviar_status_diferente(): void
    {
        $this->actingAs(User::factory()->create());
        $documento = Documento::factory()->create(['status' => 'autorizado']);

        $this->patch(route('documentos.update', $documento), [
            'status'       => 'autorizado',
            'chave_acesso' => '11111111111111111111111111111111111111111111',
        ]);

        $this->assertEquals('autorizado', $documento->fresh()->status);
    }
}
