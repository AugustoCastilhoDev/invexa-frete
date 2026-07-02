<?php

namespace Database\Factories;

use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManutencaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'veiculo_id'      => Veiculo::factory(),
            'tipo'            => $this->faker->randomElement(['preventiva', 'corretiva']),
            'descricao'       => $this->faker->sentence(3),
            'data_manutencao' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'km_veiculo'      => $this->faker->numberBetween(1000, 200000),
            'valor'           => $this->faker->randomFloat(2, 100, 2000),
            'status'          => 'concluida',
            'observacao'      => null,
        ];
    }

    public function emAndamento(): static
    {
        return $this->state(fn () => ['status' => 'em_andamento']);
    }
}
