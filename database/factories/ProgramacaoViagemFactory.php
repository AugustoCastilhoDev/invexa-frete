<?php

namespace Database\Factories;

use App\Models\Motorista;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramacaoViagemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'motorista_id'  => Motorista::factory(),
            'veiculo_id'    => Veiculo::factory(),
            'cliente_id'    => null,
            'origem'        => $this->faker->city(),
            'destino'       => $this->faker->city(),
            'data_prevista' => now()->addDays(3)->format('Y-m-d'),
            'observacoes'   => null,
            'status'        => 'pendente',
        ];
    }

    public function confirmada(): static
    {
        return $this->state(fn () => ['status' => 'confirmada']);
    }
}
