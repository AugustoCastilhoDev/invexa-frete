<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnidadeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'nome' => 'Filial ' . $this->faker->city(),
            'cnpj' => $this->faker->numerify('##.###.###/000#-##'),
            'inscricao_estadual' => $this->faker->numerify('##########'),
            'cep' => $this->faker->numerify('#####-###'),
            'logradouro' => $this->faker->streetName(),
            'numero' => $this->faker->buildingNumber(),
            'bairro' => $this->faker->word(),
            'municipio' => $this->faker->city(),
            'codigo_municipio' => $this->faker->numerify('#######'),
            'uf' => $this->faker->stateAbbr(),
            'telefone' => $this->faker->numerify('(##) ####-####'),
            'rntrc' => $this->faker->numerify('########'),
            'cfop_padrao' => '6353',
            'icms_situacao_tributaria' => '40',
            'icms_aliquota' => $this->faker->randomFloat(2, 0, 18),
        ];
    }
}
