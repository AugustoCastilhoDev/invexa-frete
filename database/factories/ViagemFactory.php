<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Motorista;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ViagemFactory extends Factory
{
    public function definition(): array
    {
        $valorFrete = $this->faker->randomFloat(2, 1000, 5000);
        $percentual = 10;
        $valorMotorista = round($valorFrete * $percentual / 100, 2);

        return [
            'motorista_id'              => Motorista::factory(),
            'veiculo_id'                => Veiculo::factory(),
            'cliente_id'                => Cliente::factory(),
            'origem'                    => $this->faker->city(),
            'destino'                   => $this->faker->city(),
            'data_saida'                => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'data_retorno'              => null,
            'km_inicial'                => $this->faker->numberBetween(0, 10000),
            'km_final'                  => null,
            'valor_frete'               => $valorFrete,
            'percentual_motorista'      => $percentual,
            'valor_motorista'           => $valorMotorista,
            'total_combustivel'         => 0,
            'total_manutencao'          => 0,
            'total_descontos'           => 0,
            'valor_adiantamento'        => 0,
            'adiantamento_descontavel'  => true,
            'saldo_motorista'           => $valorMotorista,
            'lucro_transportadora'      => round($valorFrete - $valorMotorista, 2),
            'status'                    => 'aberta',
            'observacoes'               => null,
        ];
    }

    public function encerrada(): static
    {
        return $this->state(fn () => [
            'status'       => 'encerrada',
            'data_retorno' => now(),
        ]);
    }

    public function aguardandoAcerto(): static
    {
        return $this->state(fn () => ['status' => 'aguardando_acerto']);
    }
}
