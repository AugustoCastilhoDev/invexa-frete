<?php

namespace Database\Factories;

use App\Models\ProgramacaoViagem;
use Illuminate\Database\Eloquent\Factories\Factory;

class DestinoProgramacaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'programacao_viagem_id' => ProgramacaoViagem::factory(),
            'cidade'                => $this->faker->city(),
            'uf'                    => $this->faker->stateAbbr(),
            'codigo_municipio'      => $this->faker->numerify('#######'),
            'valor_frete'           => $this->faker->randomFloat(2, 200, 2000),
            'ordem'                 => 0,
        ];
    }
}
