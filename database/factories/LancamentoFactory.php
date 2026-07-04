<?php

namespace Database\Factories;

use App\Models\Viagem;
use Illuminate\Database\Eloquent\Factories\Factory;

class LancamentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'viagem_id'       => Viagem::factory(),
            'tipo'            => $this->faker->randomElement(['combustivel', 'manutencao', 'outros']),
            'descricao'       => $this->faker->sentence(3),
            'valor'           => $this->faker->randomFloat(2, 50, 500),
            'data_lancamento' => $this->faker->dateTimeBetween('-15 days', 'now')->format('Y-m-d'),
            'comprovante'     => null,
            'observacao'      => null,
        ];
    }

    public function combustivel(): static
    {
        return $this->state(fn () => ['tipo' => 'combustivel']);
    }

    public function manutencao(): static
    {
        return $this->state(fn () => ['tipo' => 'manutencao']);
    }

    public function pendente(): static
    {
        return $this->state(fn () => ['status' => 'pendente']);
    }
}
