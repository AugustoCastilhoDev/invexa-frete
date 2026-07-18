<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnosticoTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('diagnostico.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_comum_nao_pode_acessar_diagnostico(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get(route('diagnostico.index'));

        $response->assertForbidden();
    }

    public function test_super_admin_pode_ver_diagnostico(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        Empresa::factory()->create(['status' => 'ativo']);

        $response = $this->get(route('diagnostico.index'));

        $response->assertOk();
        $response->assertSee('Diagnóstico do Sistema');
        $response->assertSee('Empresas ativas');
        $response->assertSee('Tamanho do banco');
    }
}
