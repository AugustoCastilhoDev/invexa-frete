<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Viagem;
use Illuminate\Database\Eloquent\Factories\Factory;

class CargaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'viagem_id'   => Viagem::factory(),
            'cliente_id'  => Cliente::factory(),
            'valor_frete' => $this->faker->randomFloat(2, 300, 3000),
        ];
    }
}
