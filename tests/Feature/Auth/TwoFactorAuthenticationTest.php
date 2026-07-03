<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ativar_gera_segredo_nao_confirmado(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('two-factor.enable'));

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_confirmar_com_codigo_valido_ativa_2fa_e_gera_codigos_de_recuperacao(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('two-factor.enable'));
        $user->refresh();

        $codigo = (new Google2FA())->getCurrentOtp($user->two_factor_secret);

        $response = $this->post(route('two-factor.confirm'), ['code' => $codigo]);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertTrue($user->hasEnabledTwoFactorAuthentication());
        $this->assertCount(8, $user->two_factor_recovery_codes);
    }

    public function test_confirmar_com_codigo_invalido_nao_ativa(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('two-factor.enable'));

        $response = $this->post(route('two-factor.confirm'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertFalse($user->fresh()->hasEnabledTwoFactorAuthentication());
    }

    public function test_desativar_exige_senha_correta(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->ativarDuasEtapas($user);

        $response = $this->delete(route('two-factor.disable'), ['password' => 'senha-errada']);

        $response->assertSessionHasErrorsIn('twoFactorDisable', 'password');
        $this->assertTrue($user->fresh()->hasEnabledTwoFactorAuthentication());
    }

    public function test_desativar_com_senha_correta_limpa_2fa(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->ativarDuasEtapas($user);

        $response = $this->delete(route('two-factor.disable'), ['password' => 'password']);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertFalse($user->hasEnabledTwoFactorAuthentication());
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
    }

    public function test_regenerar_codigos_de_recuperacao_substitui_os_antigos(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->ativarDuasEtapas($user);

        $codigosAntigos = $user->fresh()->two_factor_recovery_codes;

        $response = $this->post(route('two-factor.recovery-codes'));

        $response->assertRedirect(route('profile.edit'));
        $novosCodigos = $user->fresh()->two_factor_recovery_codes;
        $this->assertCount(8, $novosCodigos);
        $this->assertNotEquals($codigosAntigos, $novosCodigos);
    }

    private function ativarDuasEtapas(User $user): void
    {
        $this->post(route('two-factor.enable'));
        $user->refresh();

        $codigo = (new Google2FA())->getCurrentOtp($user->two_factor_secret);
        $this->post(route('two-factor.confirm'), ['code' => $codigo]);
        $user->refresh();
    }
}
