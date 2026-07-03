<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class TwoFactorLoginTest extends TestCase
{
    use RefreshDatabase;

    private function criarUsuarioCom2fa(): array
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user = User::factory()->create([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => ['ABCDE-12345', 'FGHIJ-67890'],
        ]);

        return [$user, $secret];
    }

    public function test_usuario_sem_2fa_loga_direto(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_usuario_com_2fa_e_redirecionado_para_o_desafio_sem_estar_logado(): void
    {
        [$user] = $this->criarUsuarioCom2fa();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('two-factor.login'));
    }

    public function test_desafio_sem_login_pendente_redireciona_para_login(): void
    {
        $response = $this->get(route('two-factor.login'));

        $response->assertRedirect(route('login'));
    }

    public function test_codigo_valido_completa_o_login(): void
    {
        [$user, $secret] = $this->criarUsuarioCom2fa();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $codigo = (new Google2FA())->getCurrentOtp($secret);
        $response = $this->post(route('two-factor.login'), ['code' => $codigo]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_codigo_invalido_nao_completa_o_login(): void
    {
        [$user] = $this->criarUsuarioCom2fa();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response = $this->post(route('two-factor.login'), ['code' => '000000']);

        $this->assertGuest();
        $response->assertSessionHasErrors('code');
    }

    public function test_codigo_de_recuperacao_valido_completa_o_login_e_e_consumido(): void
    {
        [$user] = $this->criarUsuarioCom2fa();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response = $this->post(route('two-factor.login'), ['recovery_code' => 'ABCDE-12345']);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
        $this->assertNotContains('ABCDE-12345', $user->fresh()->two_factor_recovery_codes);
    }

    public function test_codigo_de_recuperacao_ja_usado_nao_funciona_de_novo(): void
    {
        [$user] = $this->criarUsuarioCom2fa();
        // ABCDE já "usado" anteriormente — campo sensível, só muda via forceFill
        $user->forceFill(['two_factor_recovery_codes' => ['FGHIJ-67890']])->save();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $response = $this->post(route('two-factor.login'), ['recovery_code' => 'ABCDE-12345']);

        $this->assertGuest();
        $response->assertSessionHasErrors('code');
    }
}
