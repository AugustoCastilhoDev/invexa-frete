<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome'   => fake()->unique()->company(),
            'cnpj'   => fake()->unique()->numerify('##.###.###/####-##'),
            'status' => 'ativo',
        ];
    }

    public function inativa(): static
    {
        return $this->state(fn () => ['status' => 'inativo']);
    }

    public function focusNfeAtivo(): static
    {
        return $this->state(fn () => [
            'focus_nfe_ativo' => true,
            'focus_nfe_ambiente' => 'homologacao',
            'focus_nfe_token' => 'token-teste',
        ]);
    }

    public function dadosFiscaisCompletos(): static
    {
        return $this->state(fn () => [
            'cep' => '80010-000',
            'logradouro' => 'Rua das Flores',
            'numero' => '100',
            'bairro' => 'Centro',
            'municipio' => 'Curitiba',
            'codigo_municipio' => '4106902',
            'uf' => 'PR',
            'telefone' => '(41) 3333-4444',
            'inscricao_estadual' => '1234567890',
            'rntrc' => '12345678',
            'regime_tributario' => 'simples_nacional',
            'cfop_padrao' => '6353',
            'icms_situacao_tributaria' => '40',
            'icms_aliquota' => 12.00,
        ]);
    }
}
