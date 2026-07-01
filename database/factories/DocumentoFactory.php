<?php

namespace Database\Factories;

use App\Models\Viagem;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'viagem_id'    => Viagem::factory(),
            'tipo'         => $this->faker->randomElement(['cte', 'mdfe', 'nfe', 'outros']),
            'numero'       => $this->faker->numerify('######'),
            'chave_acesso' => $this->faker->numerify(str_repeat('#', 44)),
            'serie'        => '1',
            'data_emissao' => $this->faker->dateTimeBetween('-15 days', 'now')->format('Y-m-d'),
            'valor'        => $this->faker->randomFloat(2, 500, 5000),
            'status'       => 'pendente',
            'arquivo'      => null,
            'observacao'   => null,
        ];
    }
}
