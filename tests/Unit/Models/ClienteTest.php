<?php

namespace Tests\Unit\Models;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_documento_formatado_para_cpf(): void
    {
        $cliente = Cliente::factory()->fisica()->create(['cpf_cnpj' => '12345678901']);

        $this->assertEquals('123.456.789-01', $cliente->documento_formatado);
    }

    public function test_documento_formatado_para_cnpj(): void
    {
        $cliente = Cliente::factory()->create(['cpf_cnpj' => '12345678000199']);

        $this->assertEquals('12.345.678/0001-99', $cliente->documento_formatado);
    }

    public function test_documento_formatado_para_cnpj_alfanumerico(): void
    {
        // Novo formato da Receita Federal: raiz + ordem (12 primeiros
        // caracteres) podem ter letras; só os 2 dígitos verificadores finais
        // continuam numéricos.
        $cliente = Cliente::factory()->create(['cpf_cnpj' => '12.ABC.345/0001-99']);

        $this->assertEquals('12.ABC.345/0001-99', $cliente->documento_formatado);
    }

    public function test_endereco_completo_concatena_partes_preenchidas(): void
    {
        $cliente = Cliente::factory()->create([
            'logradouro'  => 'Rua das Flores',
            'numero'      => '100',
            'complemento' => null,
            'bairro'      => 'Centro',
            'cidade'      => 'Curitiba',
            'estado'      => 'PR',
        ]);

        $this->assertEquals('Rua das Flores, 100, Centro, Curitiba, PR', $cliente->endereco_completo);
    }
}
