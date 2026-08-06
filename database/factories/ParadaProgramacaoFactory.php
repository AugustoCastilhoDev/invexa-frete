<?php

namespace Database\Factories;

use App\Models\ProgramacaoViagem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParadaProgramacaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'programacao_viagem_id' => ProgramacaoViagem::factory(),
            'tipo'                  => 'entrega',
            'cidade'                => $this->faker->city(),
            'uf'                    => $this->faker->stateAbbr(),
            'codigo_municipio'      => $this->faker->numerify('#######'),
            'valor_frete'           => $this->faker->randomFloat(2, 200, 2000),
            'ordem'                 => 0,
        ];
    }

    public function coleta(): static
    {
        return $this->state(fn () => ['tipo' => 'coleta', 'valor_frete' => null]);
    }
}
