<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Motorista;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dados_criados_por_um_admin_saem_automaticamente_com_a_empresa_dele(): void
    {
        $empresaA = Empresa::factory()->create();
        $adminA   = User::factory()->admin()->create(['empresa_id' => $empresaA->id]);

        $this->actingAs($adminA)->post(route('motoristas.store'), [
            'nome'                 => 'Motorista da Empresa A',
            'cpf'                  => '111.111.111-11',
            'cnh'                  => '11111111111',
            'categoria_cnh'        => 'E',
            'percentual_comissao'  => 10,
            'status'               => 'ativo',
        ]);

        $this->assertDatabaseHas('motoristas', [
            'cpf'        => '111.111.111-11',
            'empresa_id' => $empresaA->id,
        ]);
    }

    public function test_admin_de_uma_empresa_nao_ve_motoristas_de_outra_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $adminA   = User::factory()->admin()->create(['empresa_id' => $empresaA->id]);
        $adminB   = User::factory()->admin()->create(['empresa_id' => $empresaB->id]);

        $this->actingAs($adminA);
        $motoristaA = Motorista::factory()->create(['nome' => 'Motorista Exclusivo da Empresa A']);

        $this->actingAs($adminB);
        $motoristaB = Motorista::factory()->create(['nome' => 'Motorista da Empresa B']);

        // Admin B lista só o próprio motorista
        $response = $this->get(route('motoristas.index'));
        $response->assertOk();
        $response->assertSee('Motorista da Empresa B');
        $response->assertDontSee('Motorista Exclusivo da Empresa A');

        // Admin B não consegue acessar o motorista da empresa A diretamente pela URL
        $this->get(route('motoristas.show', $motoristaA))->assertNotFound();
        $this->get(route('motoristas.edit', $motoristaA))->assertNotFound();

        // Cada empresa enxerga só a própria contagem
        $this->assertEquals(1, Motorista::count());

        $this->actingAs($adminA);
        $this->assertEquals(1, Motorista::count());
        $this->get(route('motoristas.show', $motoristaB))->assertNotFound();
    }

    public function test_admin_nao_ve_nem_acessa_usuarios_de_outra_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $adminA   = User::factory()->admin()->create(['empresa_id' => $empresaA->id]);
        $adminB   = User::factory()->admin()->create(['empresa_id' => $empresaB->id]);
        $operadorA = User::factory()->create(['empresa_id' => $empresaA->id, 'name' => 'Operador Exclusivo da Empresa A']);

        $this->actingAs($adminB);

        $response = $this->get(route('users.index'));
        $response->assertOk();
        $response->assertDontSee('Operador Exclusivo da Empresa A');

        $this->get(route('users.edit', $operadorA))->assertNotFound();
        $this->put(route('users.update', $operadorA), [
            'name'   => 'Hackeado',
            'email'  => $operadorA->email,
            'role'   => 'operador',
            'status' => 'ativo',
        ])->assertNotFound();
        $this->assertEquals('Operador Exclusivo da Empresa A', $operadorA->fresh()->name);
    }

    public function test_isolamento_tambem_vale_para_viagens_e_veiculos(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $adminA   = User::factory()->admin()->create(['empresa_id' => $empresaA->id]);
        $adminB   = User::factory()->admin()->create(['empresa_id' => $empresaB->id]);

        $this->actingAs($adminA);
        $veiculoA = Veiculo::factory()->create(['placa' => 'AAA1A11']);
        $viagemA  = Viagem::factory()->create();

        $this->actingAs($adminB);
        $veiculoB = Veiculo::factory()->create(['placa' => 'BBB2B22']);

        $response = $this->get(route('veiculos.index'));
        $response->assertSee('BBB2B22');
        $response->assertDontSee('AAA1A11');

        $this->get(route('veiculos.show', $veiculoA))->assertNotFound();
        $this->get(route('viagens.show', $viagemA))->assertNotFound();

        $this->assertEquals($empresaA->id, $viagemA->fresh()->empresa_id);
        $this->assertEquals($empresaB->id, $veiculoB->fresh()->empresa_id);
    }
}
