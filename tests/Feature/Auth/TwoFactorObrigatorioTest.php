<?php

namespace Tests\Feature\Auth;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class TwoFactorObrigatorioTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sem_2fa_e_redirecionado_para_o_perfil_ao_acessar_area_operacional(): void
    {
        $admin = User::factory()->admin()->semDoisFatores()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertRedirect(route('profile.edit'));
        $this->assertTrue(session('aviso_2fa'));
    }

    public function test_super_admin_sem_2fa_e_redirecionado_para_o_perfil_ao_acessar_empresas(): void
    {
        $superAdmin = User::factory()->superAdmin()->semDoisFatores()->create();

        $response = $this->actingAs($superAdmin)->get(route('empresas.index'));

        $response->assertRedirect(route('profile.edit'));
    }

    public function test_operador_sem_2fa_acessa_normalmente(): void
    {
        $operador = User::factory()->create(); // role padrão: operador

        $response = $this->actingAs($operador)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_admin_com_2fa_acessa_normalmente(): void
    {
        $admin = User::factory()->admin()->create(); // já vem com 2FA ativo por padrão

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_admin_sem_2fa_ainda_acessa_a_propria_tela_de_perfil(): void
    {
        $admin = User::factory()->admin()->semDoisFatores()->create();

        $this->actingAs($admin)->get(route('profile.edit'))->assertOk();
    }

    public function test_admin_sem_2fa_consegue_iniciar_a_propria_ativacao_sem_cair_em_loop_de_redirecionamento(): void
    {
        $admin = User::factory()->admin()->semDoisFatores()->create();

        $response = $this->actingAs($admin)->post(route('two-factor.enable'));

        $response->assertRedirect(route('profile.edit'));
        $this->assertNotNull($admin->fresh()->two_factor_secret);
    }

    public function test_admin_deixa_de_ser_redirecionado_apos_confirmar_o_2fa(): void
    {
        $admin = User::factory()->admin()->semDoisFatores()->create();
        $this->actingAs($admin)->post(route('two-factor.enable'));

        $secret = $admin->fresh()->two_factor_secret;
        $codigo = (new Google2FA())->getCurrentOtp($secret);
        $this->post(route('two-factor.confirm'), ['code' => $codigo]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_modo_suporte_nao_exige_2fa_do_admin_representado(): void
    {
        $superAdmin = User::factory()->superAdmin()->create(); // com 2FA, pra passar pelo próprio gate
        $empresa = Empresa::factory()->create();
        $adminSemDoisFatores = User::factory()->admin()->semDoisFatores()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($superAdmin);
        $this->post(route('empresas.suporte.iniciar', $empresa));
        $this->assertAuthenticatedAs($adminSemDoisFatores);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
    }
}
