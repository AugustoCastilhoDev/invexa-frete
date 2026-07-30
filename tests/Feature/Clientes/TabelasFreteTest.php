<?php

namespace Tests\Feature\Clientes;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\TabelaFrete;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TabelasFreteTest extends TestCase
{
    use RefreshDatabase;

    public function test_adiciona_rota_a_tabela_de_frete_do_cliente(): void
    {
        $this->actingAs(User::factory()->create());
        $cliente = Cliente::factory()->create();

        $response = $this->post(route('clientes.tabela-frete.store', $cliente), [
            'origem' => 'São Paulo',
            'origem_uf' => 'SP',
            'origem_codigo_municipio' => '3550308',
            'destino' => 'Curitiba',
            'destino_uf' => 'PR',
            'destino_codigo_municipio' => '4106902',
            'valor_frete' => '2500.00',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tabelas_frete', [
            'cliente_id' => $cliente->id,
            'origem_codigo_municipio' => '3550308',
            'destino_codigo_municipio' => '4106902',
            'valor_frete' => '2500.00',
        ]);
    }

    public function test_nao_permite_rota_duplicada_para_o_mesmo_cliente(): void
    {
        $this->actingAs(User::factory()->create());
        $cliente = Cliente::factory()->create();
        TabelaFrete::factory()->create(['cliente_id' => $cliente->id]);

        $response = $this->post(route('clientes.tabela-frete.store', $cliente), [
            'origem' => 'São Paulo',
            'origem_uf' => 'SP',
            'origem_codigo_municipio' => '3550308',
            'destino' => 'Rio de Janeiro',
            'destino_uf' => 'RJ',
            'destino_codigo_municipio' => '3304557',
            'valor_frete' => '999.00',
        ]);

        $response->assertSessionHasErrors('origem');
        $this->assertSame(1, TabelaFrete::where('cliente_id', $cliente->id)->count());
    }

    public function test_remove_rota_da_tabela_de_frete(): void
    {
        $this->actingAs(User::factory()->create());
        $rota = TabelaFrete::factory()->create();

        $response = $this->delete(route('tabela-frete.destroy', $rota));

        $response->assertRedirect();
        $this->assertDatabaseMissing('tabelas_frete', ['id' => $rota->id]);
    }

    public function test_tabela_de_frete_de_uma_empresa_nao_e_acessivel_por_outra(): void
    {
        $rota = TabelaFrete::factory()->create();

        $outraEmpresa = Empresa::factory()->create();
        $outroUsuario = User::factory()->create(['empresa_id' => $outraEmpresa->id]);
        $this->actingAs($outroUsuario);

        $response = $this->delete(route('tabela-frete.destroy', $rota));

        $response->assertNotFound();
        $this->assertDatabaseHas('tabelas_frete', ['id' => $rota->id]);
    }

    public function test_sugestao_retorna_valor_quando_rota_cadastrada(): void
    {
        $this->actingAs(User::factory()->create());
        $rota = TabelaFrete::factory()->create(['valor_frete' => '1800.50']);

        $response = $this->getJson(route('tabela-frete.sugestao', [
            'cliente_id' => $rota->cliente_id,
            'origem_codigo_municipio' => $rota->origem_codigo_municipio,
            'destino_codigo_municipio' => $rota->destino_codigo_municipio,
        ]));

        $response->assertOk()->assertJson(['valor' => '1800.50']);
    }

    public function test_sugestao_retorna_null_quando_rota_nao_cadastrada(): void
    {
        $this->actingAs(User::factory()->create());
        $cliente = Cliente::factory()->create();

        $response = $this->getJson(route('tabela-frete.sugestao', [
            'cliente_id' => $cliente->id,
            'origem_codigo_municipio' => '9999999',
            'destino_codigo_municipio' => '8888888',
        ]));

        $response->assertOk()->assertJson(['valor' => null]);
    }
}
