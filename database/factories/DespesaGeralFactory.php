<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DespesaGeralFactory extends Factory
{
    public function definition(): array
    {
        return [
            'categoria'    => $this->faker->randomElement(['aluguel', 'salarios', 'contas', 'seguro', 'impostos', 'marketing', 'outros']),
            'descricao'    => $this->faker->sentence(3),
            'valor'        => $this->faker->randomFloat(2, 100, 5000),
            'data_despesa' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'recorrente'   => false,
            'observacao'   => null,
        ];
    }

    public function recorrente(): static
    {
        return $this->state(fn () => ['recorrente' => true]);
    }
}
