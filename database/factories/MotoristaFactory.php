<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MotoristaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome'                => $this->faker->name(),
            'cpf'                 => $this->faker->unique()->numerify('###########'),
            'cnh'                 => $this->faker->numerify('###########'),
            'categoria_cnh'       => $this->faker->randomElement(['A', 'B', 'AB', 'C', 'D', 'E']),
            'validade_cnh'        => $this->faker->dateTimeBetween('now', '+3 years')->format('Y-m-d'),
            'telefone'            => $this->faker->numerify('(##) #####-####'),
            'email'               => $this->faker->unique()->safeEmail(),
            'percentual_comissao' => 10,
            'status'              => 'ativo',
        ];
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['status' => 'inativo']);
    }
}
