<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'operador',
            'status' => 'ativo',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin'])->comDoisFatoresAtivos();
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['role' => 'super_admin', 'empresa_id' => null])->comDoisFatoresAtivos();
    }

    /**
     * 2FA é obrigatório pra admin/super_admin (EnsureTwoFactorIsEnabled) —
     * sem isso, qualquer teste que crie um desses papéis e acesse uma rota
     * operacional cairia no redirecionamento de "configure seu 2FA" em vez
     * de chegar na tela sendo testada. Representa um admin já onboardado.
     */
    public function comDoisFatoresAtivos(): static
    {
        return $this->state(function () {
            return [
                'two_factor_secret' => (new Google2FA())->generateSecretKey(),
                'two_factor_confirmed_at' => now(),
                'two_factor_recovery_codes' => ['ABCDE-12345', 'FGHIJ-67890'],
            ];
        });
    }

    /**
     * Pra testar especificamente o fluxo de admin/super_admin que ainda não
     * configurou o 2FA obrigatório (redirecionamento pra tela de perfil).
     */
    public function semDoisFatores(): static
    {
        return $this->state(fn () => [
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['status' => 'inativo']);
    }
}
