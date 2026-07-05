<?php

namespace Tests\Feature;

use App\Models\DespesaGeral;
use App\Models\Manutencao;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DreTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('dre.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_dre_considera_apenas_viagens_encerradas_do_periodo(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $dataDentro = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $viagem = Viagem::factory()->encerrada()->create([
            'data_saida'          => $dataDentro,
            'valor_frete'         => 2000,
            'percentual_motorista'=> 10,
            'valor_motorista'     => 200,
            'total_combustivel'   => 100,
            'total_manutencao'    => 50,
        ]);

        // não deve entrar: aberta
        Viagem::factory()->create([
            'status'      => 'aberta',
            'data_saida'  => $dataDentro,
            'valor_frete' => 9999,
        ]);

        // não deve entrar: fora do período
        Viagem::factory()->encerrada()->create([
            'data_saida'  => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'valor_frete' => 9999,
        ]);

        $response = $this->get(route('dre.index'));

        $response->assertOk();
        $response->assertViewHas('receitaBruta', 2000.0);
        $response->assertViewHas('comissaoMotoristas', 200.0);
        $response->assertViewHas('combustivel', 100.0);
        $response->assertViewHas('manutencaoViagem', 50.0);
        $response->assertViewHas('resultadoBruto', 2000.0 - 200.0 - 100.0 - 50.0);
    }

    public function test_dre_inclui_manutencao_avulsa_e_despesas_gerais_como_despesas_operacionais(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $dataDentro = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $veiculo = Veiculo::factory()->create();
        Manutencao::factory()->create([
            'veiculo_id'      => $veiculo->id,
            'data_manutencao' => $dataDentro,
            'valor'           => 300,
        ]);

        DespesaGeral::factory()->create([
            'categoria'    => 'aluguel',
            'valor'        => 1500,
            'data_despesa' => $dataDentro,
        ]);

        DespesaGeral::factory()->create([
            'categoria'    => 'salarios',
            'valor'        => 4000,
            'data_despesa' => $dataDentro,
        ]);

        $response = $this->get(route('dre.index'));

        $response->assertOk();
        $response->assertViewHas('manutencaoFrota', 300.0);
        $response->assertViewHas('despesasGerais', 5500.0);
        $response->assertViewHas('despesasOperacionais', 300.0 + 5500.0);
        $response->assertViewHas('despesasPorCategoria', function ($porCategoria) {
            return $porCategoria->count() === 2;
        });
    }

    public function test_pdf_gera_documento_do_dre(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Viagem::factory()->encerrada()->create(['data_saida' => now()->format('Y-m-d')]);

        $response = $this->get(route('dre.pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_operador_nao_pode_acessar_dre(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('dre.index'));

        $response->assertForbidden();
    }
}
