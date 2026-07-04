<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome'   => fake()->unique()->company(),
            'cnpj'   => fake()->unique()->numerify('##.###.###/####-##'),
            'status' => 'ativo',
        ];
    }

    public function inativa(): static
    {
        return $this->state(fn () => ['status' => 'inativo']);
    }
}
