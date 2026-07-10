<?php

namespace Database\Factories;

use App\Models\Viagem;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmissaoFiscalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'viagem_id'  => Viagem::factory(),
            'tipo'       => $this->faker->randomElement(['cte', 'mdfe']),
            'referencia' => 'test-' . $this->faker->unique()->regexify('[A-Za-z0-9]{16}'),
            'status'     => 'processando_autorizacao',
        ];
    }

    public function autorizada(): static
    {
        return $this->state(fn () => [
            'status' => 'autorizado',
            'chave_acesso' => $this->faker->numerify(str_repeat('#', 44)),
            'numero' => $this->faker->numerify('######'),
            'autorizado_em' => now(),
        ]);
    }
}
