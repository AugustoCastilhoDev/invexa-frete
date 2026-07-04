<?php

namespace Tests\Feature\Empresas;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuporteTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_pode_iniciar_suporte_e_passa_a_navegar_como_o_admin_da_empresa(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $empresa    = Empresa::factory()->create();
        $admin      = User::factory()->admin()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($superAdmin);

        $response = $this->post(route('empresas.suporte.iniciar', $empresa));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->assertEquals($superAdmin->id, session('suporte_super_admin_id'));

        // Enquanto em modo suporte, acessa normalmente as telas operacionais da empresa
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_admin_comum_nao_pode_iniciar_suporte(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $empresa = Empresa::factory()->create();

        $response = $this->post(route('empresas.suporte.iniciar', $empresa));

        $response->assertForbidden();
    }

    public function test_nao_da_suporte_a_empresa_sem_admin_ativo(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create();

        $response = $this->post(route('empresas.suporte.iniciar', $empresa));

        $response->assertNotFound();
    }

    public function test_encerrar_suporte_volta_para_o_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $empresa    = Empresa::factory()->create();
        $admin      = User::factory()->admin()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($superAdmin);
        $this->post(route('empresas.suporte.iniciar', $empresa));
        $this->assertAuthenticatedAs($admin);

        $response = $this->post(route('suporte.encerrar'));

        $response->assertRedirect(route('empresas.index'));
        $this->assertAuthenticatedAs($superAdmin);
        $this->assertNull(session('suporte_super_admin_id'));
    }

    public function test_nao_pode_encerrar_suporte_sem_uma_sessao_de_suporte_ativa(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->post(route('suporte.encerrar'));

        $response->assertForbidden();
    }
}
