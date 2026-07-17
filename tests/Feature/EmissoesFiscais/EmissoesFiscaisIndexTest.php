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

    public function test_cte_lista_apenas_cte(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte', 'numero' => '111']);
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'mdfe', 'numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.cte'));

        $response->assertOk();
        $response->assertSee('111');
        $response->assertDontSee('222');
    }

    public function test_mdfe_lista_apenas_mdfe(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte', 'numero' => '111']);
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'mdfe', 'numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.mdfe'));

        $response->assertOk();
        $response->assertSee('222');
        $response->assertDontSee('111');
    }

    public function test_mdfe_filtra_por_status(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'mdfe', 'numero' => '111']);
        EmissaoFiscal::factory()->encerrada()->create(['numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.mdfe', ['status' => 'encerrado']));

        $response->assertOk();
        $response->assertSee('222');
        $response->assertDontSee('111');
    }

    public function test_cte_filtra_por_veiculo(): void
    {
        $this->actingAs(User::factory()->create());
        $viagemA = Viagem::factory()->create();
        $viagemB = Viagem::factory()->create();
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte', 'viagem_id' => $viagemA->id, 'numero' => '111']);
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte', 'viagem_id' => $viagemB->id, 'numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.cte', ['veiculo_id' => $viagemA->veiculo_id]));

        $response->assertOk();
        $response->assertSee('111');
        $response->assertDontSee('222');
    }

    public function test_cte_pagina_15_por_pagina(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->count(20)->create(['tipo' => 'cte']);

        $response = $this->get(route('emissoes-fiscais.cte'));

        $response->assertOk();
        $response->assertViewHas('emissoes', function ($emissoes) {
            return $emissoes->count() === 15 && $emissoes->total() === 20;
        });
    }

    public function test_cte_isola_emissoes_por_empresa(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte', 'numero' => '111']);

        $outraEmpresa = Empresa::factory()->create();
        $outroUsuario = User::factory()->create(['empresa_id' => $outraEmpresa->id]);
        $this->actingAs($outroUsuario);
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte', 'numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.cte'));

        $response->assertOk();
        $response->assertSee('222');
        $response->assertDontSee('111');
    }

    public function test_cte_filtra_por_cliente(): void
    {
        $this->actingAs(User::factory()->create());
        $clienteA = Cliente::factory()->create(['nome' => 'Cliente A']);
        $clienteB = Cliente::factory()->create(['nome' => 'Cliente B']);
        $cargaA = Carga::factory()->create(['cliente_id' => $clienteA->id]);
        $cargaB = Carga::factory()->create(['cliente_id' => $clienteB->id]);
        EmissaoFiscal::factory()->autorizada()->paraCarga($cargaA)->create(['numero' => '111']);
        EmissaoFiscal::factory()->autorizada()->paraCarga($cargaB)->create(['numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.cte', ['cliente_id' => $clienteA->id]));

        $response->assertOk();
        $response->assertSee('111');
        $response->assertDontSee('222');
    }

    public function test_cte_csv_exporta_emissoes(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte', 'numero' => '111']);
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'mdfe', 'numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.cte.csv'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $conteudo = $response->streamedContent();
        $this->assertStringContainsString('111', $conteudo);
        $this->assertStringNotContainsString('222', $conteudo);
    }

    public function test_mdfe_csv_exporta_emissoes(): void
    {
        $this->actingAs(User::factory()->create());
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte', 'numero' => '111']);
        EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'mdfe', 'numero' => '222']);

        $response = $this->get(route('emissoes-fiscais.mdfe.csv'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $conteudo = $response->streamedContent();
        $this->assertStringContainsString('222', $conteudo);
        $this->assertStringNotContainsString('111', $conteudo);
    }
}
