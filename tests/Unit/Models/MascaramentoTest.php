<?php

namespace Tests\Unit\Models;

use App\Models\Cliente;
use App\Models\Motorista;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MascaramentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_cpf_do_motorista_e_mascarado(): void
    {
        $motorista = Motorista::factory()->create(['cpf' => '12345678901']);

        $this->assertEquals('123.***.***-01', $motorista->cpf_mascarado);
    }

    public function test_cnh_do_motorista_e_mascarada(): void
    {
        $motorista = Motorista::factory()->create(['cnh' => '123456789']);

        $this->assertEquals('12*****89', $motorista->cnh_mascarada);
    }

    public function test_cnh_nula_retorna_nulo(): void
    {
        $motorista = Motorista::factory()->create(['cnh' => null]);

        $this->assertNull($motorista->cnh_mascarada);
    }

    public function test_documento_de_cliente_pessoa_fisica_e_mascarado(): void
    {
        $cliente = Cliente::factory()->fisica()->create(['cpf_cnpj' => '12345678901']);

        $this->assertEquals('123.***.***-01', $cliente->documento_mascarado);
    }

    public function test_documento_de_cliente_pessoa_juridica_nao_e_mascarado(): void
    {
        $cliente = Cliente::factory()->create(['cpf_cnpj' => '12345678000199']);

        $this->assertEquals($cliente->documento_formatado, $cliente->documento_mascarado);
    }
}
