<?php

namespace Tests\Feature\Empresas;

use App\Models\Carga;
use App\Models\Empresa;
use App\Models\Unidade;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnidadesTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_cria_unidade_para_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create();

        $response = $this->post(route('unidades.store', $empresa), [
            'nome' => 'Filial — Minas Gerais',
            'cnpj' => '12.345.678/0002-00',
            'uf' => 'MG',
        ]);

        $response->assertRedirect(route('empresas.show', $empresa));
        $this->assertDatabaseHas('unidades', [
            'empresa_id' => $empresa->id,
            'nome' => 'Filial — Minas Gerais',
            'uf' => 'MG',
        ]);
    }

    public function test_admin_comum_nao_pode_criar_unidade(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $empresa = Empresa::factory()->create();

        $response = $this->post(route('unidades.store', $empresa), [
            'nome' => 'Filial — Minas Gerais',
        ]);

        $response->assertForbidden();
    }

    public function test_super_admin_atualiza_unidade(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $unidade = Unidade::factory()->create(['nome' => 'Matriz']);

        $response = $this->patch(route('unidades.update', $unidade), [
            'nome' => 'Matriz — São Paulo',
            'cfop_padrao' => '6353',
        ]);

        $response->assertRedirect(route('empresas.show', $unidade->empresa));
        $this->assertSame('Matriz — São Paulo', $unidade->fresh()->nome);
        $this->assertSame('6353', $unidade->fresh()->cfop_padrao);
    }

    public function test_nao_remove_unidade_referenciada_por_carga(): void
    {
        // Cria a carga (e a cadeia de factories dela — motorista/veículo) como
        // usuário comum, já que super_admin não tem empresa_id e quebraria as
        // tabelas tenant-scoped; troca pra super_admin só na hora do delete.
        $unidade = Unidade::factory()->create();
        $this->actingAs(User::factory()->create(['empresa_id' => $unidade->empresa_id]));
        Carga::factory()->create(['unidade_id' => $unidade->id]);

        $this->actingAs(User::factory()->superAdmin()->create());
        $response = $this->delete(route('unidades.destroy', $unidade));

        $response->assertStatus(422);
        $this->assertDatabaseHas('unidades', ['id' => $unidade->id]);
    }

    public function test_nao_remove_unidade_referenciada_por_viagem(): void
    {
        $unidade = Unidade::factory()->create();
        $this->actingAs(User::factory()->create(['empresa_id' => $unidade->empresa_id]));
        Viagem::factory()->create(['unidade_id' => $unidade->id]);

        $this->actingAs(User::factory()->superAdmin()->create());
        $response = $this->delete(route('unidades.destroy', $unidade));

        $response->assertStatus(422);
        $this->assertDatabaseHas('unidades', ['id' => $unidade->id]);
    }

    public function test_remove_unidade_sem_uso(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $unidade = Unidade::factory()->create();

        $response = $this->delete(route('unidades.destroy', $unidade));

        $response->assertRedirect(route('empresas.show', $unidade->empresa));
        $this->assertDatabaseMissing('unidades', ['id' => $unidade->id]);
    }
}
