<?php

namespace Tests\Feature\Empresas;

use App\Models\Empresa;
use App\Models\Motorista;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('empresas.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_comum_nao_pode_acessar_gestao_de_empresas(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get(route('empresas.index'));

        $response->assertForbidden();
    }

    public function test_super_admin_pode_listar_empresas(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        Empresa::factory()->create(['nome' => 'Transportadora Teste']);

        $response = $this->get(route('empresas.index'));

        $response->assertOk();
        $response->assertSee('Transportadora Teste');
    }

    public function test_super_admin_pode_criar_empresa_com_admin_inicial(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $response = $this->post(route('empresas.store'), [
            'nome'                          => 'Transportadora Nova',
            'cnpj'                          => '11.222.333/0001-44',
            'admin_name'                    => 'Admin da Nova',
            'admin_email'                   => 'admin@nova.com',
            'admin_password'                => 'senha12345',
            'admin_password_confirmation'   => 'senha12345',
        ]);

        $response->assertRedirect(route('empresas.index'));

        $empresa = Empresa::where('nome', 'Transportadora Nova')->firstOrFail();
        $this->assertDatabaseHas('users', [
            'email'      => 'admin@nova.com',
            'role'       => 'admin',
            'empresa_id' => $empresa->id,
        ]);
    }

    public function test_super_admin_pode_visualizar_detalhes_de_uma_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create(['nome' => 'Transportadora Detalhada']);
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id, 'name' => 'Admin da Detalhada']);
        Motorista::factory()->create(['empresa_id' => $empresa->id]);

        $response = $this->get(route('empresas.show', $empresa));

        $response->assertOk();
        $response->assertSee('Transportadora Detalhada');
        $response->assertSee('Admin da Detalhada');
        $response->assertViewHas('resumo', fn ($resumo) => $resumo['motoristas'] === 1);
    }

    public function test_admin_comum_nao_pode_visualizar_detalhes_de_empresa(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $empresa = Empresa::factory()->create();

        $response = $this->get(route('empresas.show', $empresa));

        $response->assertForbidden();
    }

    public function test_super_admin_pode_editar_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create(['nome' => 'Nome Antigo']);

        $response = $this->put(route('empresas.update', $empresa), [
            'nome' => 'Nome Novo',
            'cnpj' => $empresa->cnpj,
        ]);

        $response->assertRedirect(route('empresas.index'));
        $this->assertEquals('Nome Novo', $empresa->fresh()->nome);
    }

    public function test_super_admin_pode_definir_limite_de_veiculos_ao_criar_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->post(route('empresas.store'), [
            'nome'                        => 'Transportadora Pequena',
            'limite_veiculos'             => 5,
            'admin_name'                  => 'Admin Pequena',
            'admin_email'                 => 'admin@pequena.com',
            'admin_password'              => 'senha12345',
            'admin_password_confirmation' => 'senha12345',
        ]);

        $this->assertDatabaseHas('empresas', ['nome' => 'Transportadora Pequena', 'limite_veiculos' => 5]);
    }

    public function test_super_admin_pode_alterar_limite_de_veiculos_de_uma_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create(['limite_veiculos' => 5]);

        $this->put(route('empresas.update', $empresa), [
            'nome'            => $empresa->nome,
            'cnpj'            => $empresa->cnpj,
            'limite_veiculos' => 10,
        ]);

        $this->assertEquals(10, $empresa->fresh()->limite_veiculos);
    }

    public function test_super_admin_pode_desativar_e_reativar_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create();

        $this->patch(route('empresas.toggle-status', $empresa));
        $this->assertEquals('inativo', $empresa->fresh()->status);

        $this->patch(route('empresas.toggle-status', $empresa));
        $this->assertEquals('ativo', $empresa->fresh()->status);
    }

    public function test_admin_de_empresa_desativada_nao_consegue_logar(): void
    {
        $empresa = Empresa::factory()->inativa()->create();
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id]);

        $response = $this->post('/login', [
            'email'    => $admin->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
