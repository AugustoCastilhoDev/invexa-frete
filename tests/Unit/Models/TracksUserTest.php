<?php

namespace Tests\Unit\Models;

use App\Models\Desconto;
use App\Models\Lancamento;
use App\Models\Motorista;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TracksUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_criar_registro_autenticado_preenche_created_by_e_updated_by(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $motorista = Motorista::factory()->create();

        $this->assertEquals($user->id, $motorista->created_by);
        $this->assertEquals($user->id, $motorista->updated_by);
    }

    public function test_criar_registro_sem_usuario_autenticado_nao_preenche_created_by(): void
    {
        $motorista = Motorista::factory()->create();

        $this->assertNull($motorista->created_by);
        $this->assertNull($motorista->updated_by);
    }

    public function test_atualizar_registro_atualiza_apenas_updated_by(): void
    {
        $criador = User::factory()->create();
        $this->actingAs($criador);
        $veiculo = Veiculo::factory()->create();

        $editor = User::factory()->create();
        $this->actingAs($editor);
        $veiculo->update(['modelo' => 'Novo Modelo']);

        $this->assertEquals($criador->id, $veiculo->created_by);
        $this->assertEquals($editor->id, $veiculo->updated_by);
    }

    public function test_excluir_registro_soft_delete_preenche_deleted_by(): void
    {
        $criador = User::factory()->create();
        $this->actingAs($criador);
        $motorista = Motorista::factory()->create();

        $excluidor = User::factory()->create();
        $this->actingAs($excluidor);
        $motorista->delete();

        $this->assertEquals($excluidor->id, $motorista->fresh()->deleted_by);
    }

    public function test_lancamento_e_desconto_registram_quem_lancou(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $lancamento = Lancamento::factory()->create();
        $desconto = Desconto::factory()->create();

        $this->assertEquals($user->id, $lancamento->created_by);
        $this->assertEquals($user->id, $desconto->created_by);
    }

    public function test_relacao_criado_por_resolve_mesmo_apos_usuario_inativado(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $motorista = Motorista::factory()->create();

        $user->delete(); // soft delete (inativação)

        $this->assertTrue($motorista->fresh()->criadoPor->is($user));
    }
}
