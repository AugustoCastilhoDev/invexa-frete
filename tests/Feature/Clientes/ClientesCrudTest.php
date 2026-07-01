<?php

namespace Tests\Feature\Clientes;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientesCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_cliente_com_dados_validos(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('clientes.store'), [
            'tipo_pessoa' => 'juridica',
            'nome'        => 'Transportes ABC',
            'cpf_cnpj'    => '12345678000199',
            'status'      => 'ativo',
        ]);

        $response->assertRedirect(route('clientes.index'));
        $this->assertDatabaseHas('clientes', ['cpf_cnpj' => '12345678000199']);
    }

    public function test_nao_permite_cpf_cnpj_duplicado(): void
    {
        $this->actingAs(User::factory()->create());
        Cliente::factory()->create(['cpf_cnpj' => '99988877000166']);

        $response = $this->post(route('clientes.store'), [
            'tipo_pessoa' => 'juridica',
            'nome'        => 'Outra Empresa',
            'cpf_cnpj'    => '99988877000166',
            'status'      => 'ativo',
        ]);

        $response->assertSessionHasErrors('cpf_cnpj');
    }

    public function test_busca_filtra_por_nome_documento_cidade_ou_telefone(): void
    {
        $this->actingAs(User::factory()->create());

        $encontrado = Cliente::factory()->create(['cidade' => 'Florianópolis']);
        Cliente::factory()->create(['cidade' => 'Recife']);

        $response = $this->get(route('clientes.index', ['busca' => 'Florianópolis']));

        $response->assertOk();
        $response->assertViewHas('clientes', function ($clientes) use ($encontrado) {
            return $clientes->total() === 1 && $clientes->first()->is($encontrado);
        });
    }

    public function test_exclusao_e_soft_delete(): void
    {
        $this->actingAs(User::factory()->create());
        $cliente = Cliente::factory()->create();

        $response = $this->delete(route('clientes.destroy', $cliente));

        $response->assertRedirect(route('clientes.index'));
        $this->assertSoftDeleted($cliente);
    }
}
