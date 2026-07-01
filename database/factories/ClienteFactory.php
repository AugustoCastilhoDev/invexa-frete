<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tipo_pessoa'  => 'juridica',
            'nome'         => $this->faker->company(),
            'razao_social' => $this->faker->company() . ' LTDA',
            'cpf_cnpj'     => $this->faker->unique()->numerify('##############'),
            'ie'           => $this->faker->numerify('#########'),
            'email'        => $this->faker->unique()->companyEmail(),
            'telefone'     => $this->faker->numerify('(##) ####-####'),
            'celular'      => $this->faker->numerify('(##) #####-####'),
            'contato'      => $this->faker->name(),
            'cep'          => $this->faker->numerify('#####-###'),
            'logradouro'   => $this->faker->streetName(),
            'numero'       => (string) $this->faker->numberBetween(1, 9999),
            'complemento'  => null,
            'bairro'       => $this->faker->word(),
            'cidade'       => $this->faker->city(),
            'estado'       => $this->faker->randomElement(['SP', 'RJ', 'MG', 'PR', 'RS']),
            'tabela_frete' => $this->faker->randomFloat(2, 1, 10),
            'observacoes'  => null,
            'status'       => 'ativo',
        ];
    }

    public function fisica(): static
    {
        return $this->state(fn () => [
            'tipo_pessoa'  => 'fisica',
            'razao_social' => null,
            'cpf_cnpj'     => $this->faker->unique()->numerify('###########'),
            'ie'           => null,
        ]);
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['status' => 'inativo']);
    }
}
