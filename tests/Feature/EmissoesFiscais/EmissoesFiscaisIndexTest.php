<?php

namespace Tests\Feature\EmissoesFiscais;

use App\Models\Carga;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\EmissaoFiscal;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmissoesFiscaisIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lista_cte_e_mdfe(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte', 'numero' => '111']);
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'mdfe', 'numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.index'));

        $response->assertOk();
        $response->assertSee('111');
        $response->assertSee('222');
    }

    public function test_index_filtra_por_tipo(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte', 'numero' => '111']);
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'mdfe', 'numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.index', ['tipo' => 'mdfe']));

        $response->assertOk();
        $response->assertSee('222');
        $response->assertDontSee('111');
    }

    public function test_index_filtra_por_status(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte', 'numero' => '111']);
        EmissaoFiscal::factory()->encerrada()->create(['numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.index', ['status' => 'encerrado']));

        $response->assertOk();
        $response->assertSee('222');
        $response->assertDontSee('111');
    }

    public function test_index_filtra_por_veiculo(): void
    {
        $this->actingAs(User::factory()->create());
        $viagemA = Viagem::factory()->create();
        $viagemB = Viagem::factory()->create();
        EmissaoFiscal::factory()->autorizada()->create(['viagem_id' => $viagemA->id, 'numero' => '111']);
        EmissaoFiscal::factory()->autorizada()->create(['viagem_id' => $viagemB->id, 'numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.index', ['veiculo_id' => $viagemA->veiculo_id]));

        $response->assertOk();
        $response->assertSee('111');
        $response->assertDontSee('222');
    }

    public function test_index_pagina_15_por_pagina(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->count(20)->create();

        $response = $this->get(route('emissoes-fiscais.index'));

        $response->assertOk();
        $response->assertViewHas('emissoes', function ($emissoes) {
            return $emissoes->count() === 15 && $emissoes->total() === 20;
        });
    }

    public function test_index_isola_emissoes_por_empresa(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->autorizada()->create(['numero' => '111']);

        $outraEmpresa = Empresa::factory()->create();
        $outroUsuario = User::factory()->create(['empresa_id' => $outraEmpresa->id]);
        $this->actingAs($outroUsuario);
        EmissaoFiscal::factory()->autorizada()->create(['numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.index'));

        $response->assertOk();
        $response->assertSee('222');
        $response->assertDontSee('111');
    }

    public function test_index_filtra_por_cliente(): void
    {
        $this->actingAs(User::factory()->create());
        $clienteA = Cliente::factory()->create(['nome' => 'Cliente A']);
        $clienteB = Cliente::factory()->create(['nome' => 'Cliente B']);
        $cargaA = Carga::factory()->create(['cliente_id' => $clienteA->id]);
        $cargaB = Carga::factory()->create(['cliente_id' => $clienteB->id]);
        EmissaoFiscal::factory()->autorizada()->paraCarga($cargaA)->create(['numero' => '111']);
        EmissaoFiscal::factory()->autorizada()->paraCarga($cargaB)->create(['numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.index', ['cliente_id' => $clienteA->id]));

        $response->assertOk();
        $response->assertSee('111');
        $response->assertDontSee('222');
    }

    public function test_csv_exporta_emissoes(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->autorizada()->create(['numero' => '111']);

        $response = $this->get(route('emissoes-fiscais.csv'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
