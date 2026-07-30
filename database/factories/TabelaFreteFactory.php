<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class TabelaFreteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'origem' => 'São Paulo',
            'origem_uf' => 'SP',
            'origem_codigo_municipio' => '3550308',
            'destino' => 'Rio de Janeiro',
            'destino_uf' => 'RJ',
            'destino_codigo_municipio' => '3304557',
            'valor_frete' => $this->faker->randomFloat(2, 500, 5000),
        ];
    }
}
