<?php

namespace Database\Factories;

use App\Models\Carga;
use App\Models\Viagem;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmissaoFiscalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'viagem_id'  => Viagem::factory(),
            'tipo'       => $this->faker->randomElement(['cte', 'mdfe']),
            'referencia' => 'test-' . $this->faker->unique()->regexify('[A-Za-z0-9]{16}'),
            'status'     => 'processando_autorizacao',
        ];
    }

    public function autorizada(): static
    {
        return $this->state(fn () => [
            'status' => 'autorizado',
            'chave_acesso' => $this->faker->numerify(str_repeat('#', 44)),
            'numero' => $this->faker->numerify('######'),
            'autorizado_em' => now(),
        ]);
    }

    public function encerrada(): static
    {
        return $this->autorizada()->state(fn () => [
            'tipo' => 'mdfe',
            'status' => 'encerrado',
            'protocolo_encerramento' => $this->faker->numerify(str_repeat('#', 15)),
            'encerrado_em' => now(),
        ]);
    }

    // CT-e vinculado a uma carga já existente — mantém viagem_id em sincronia
    // com a viagem da carga (mesmo padrão denormalizado usado pelo controller).
    public function paraCarga(Carga $carga): static
    {
        return $this->state(fn () => [
            'tipo'      => 'cte',
            'carga_id'  => $carga->id,
            'viagem_id' => $carga->viagem_id,
        ]);
    }
}
