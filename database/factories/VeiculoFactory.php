<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VeiculoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'placa'         => strtoupper($this->faker->unique()->bothify('???#?##')),
            'modelo'        => $this->faker->randomElement(['FH 540', 'Actros', 'Constellation', 'Delivery']),
            'marca'         => $this->faker->randomElement(['Volvo', 'Mercedes-Benz', 'Volkswagen', 'Scania']),
            'ano'           => $this->faker->numberBetween(2010, 2025),
            'tipo'          => $this->faker->randomElement(['truck', 'carreta', 'van', 'utilitario', 'outro']),
            'renavam'       => $this->faker->numerify('###########'),
            'capacidade_kg' => $this->faker->randomFloat(2, 1000, 30000),
            'status'        => 'ativo',
        ];
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['status' => 'inativo']);
    }

    public function emManutencao(): static
    {
        return $this->state(fn () => ['status' => 'manutencao']);
    }

    public function carreta(): static
    {
        return $this->state(fn () => ['tipo' => 'carreta']);
    }

    public function vinculadaA(\App\Models\Veiculo $cavalo): static
    {
        return $this->state(fn () => ['tipo' => 'carreta', 'cavalo_id' => $cavalo->id]);
    }
}
