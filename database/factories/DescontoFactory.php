<?php

namespace Database\Factories;

use App\Models\Viagem;
use Illuminate\Database\Eloquent\Factories\Factory;

class DescontoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'viagem_id'     => Viagem::factory(),
            'tipo'          => $this->faker->randomElement(['vale', 'multa', 'adiantamento', 'outros']),
            'descricao'     => $this->faker->sentence(3),
            'valor'         => $this->faker->randomFloat(2, 20, 300),
            'data_desconto' => $this->faker->dateTimeBetween('-15 days', 'now')->format('Y-m-d'),
            'observacao'    => null,
        ];
    }
}
