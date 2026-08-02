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

class CustoFrotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('custo-frota.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operador_nao_pode_acessar_custo_frota(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('custo-frota.index'));

        $response->assertForbidden();
    }

    public function test_atribui_custo_direto_de_viagem_ao_veiculo_certo(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $dataDentro = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $veiculo = Veiculo::factory()->create();

        Viagem::factory()->encerrada()->create([
            'veiculo_id'        => $veiculo->id,
            'data_saida'        => $dataDentro,
            'km_inicial'        => 1000,
            'km_final'          => 1500,
            'valor_frete'       => 3000,
            'total_combustivel' => 400,
            'total_manutencao'  => 100,
        ]);

        // não deve entrar: outro veículo
        Viagem::factory()->encerrada()->create([
            'data_saida'        => $dataDentro,
            'total_combustivel' => 9999,
        ]);

        // não deve entrar: fora do período
        Viagem::factory()->encerrada()->create([
            'veiculo_id'        => $veiculo->id,
            'data_saida'        => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'total_combustivel' => 9999,
        ]);

        $response = $this->get(route('custo-frota.index'));
        $response->assertOk();

        $linha = $response->viewData('linhas')->first(fn ($l) => $l['veiculo']->id === $veiculo->id);

        $this->assertNotNull($linha);
        $this->assertEquals(500, $linha['kmRodados']);
        $this->assertEquals(500.0, $linha['custoDireto']);
        $this->assertEquals(3000.0, $linha['receita']);
        $this->assertEquals(1.0, $linha['custoPorKm']);
        $this->assertEquals(6.0, $linha['receitaPorKm']);
        $this->assertEquals(5.0, $linha['margemPorKm']);
    }

    public function test_manutencao_avulsa_entra_no_custo_do_veiculo(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $dataDentro = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $veiculo = Veiculo::factory()->create();

        Manutencao::factory()->create([
            'veiculo_id'      => $veiculo->id,
            'data_manutencao' => $dataDentro,
            'valor'           => 700,
        ]);

        $response = $this->get(route('custo-frota.index'));
        $linha = $response->viewData('linhas')->first(fn ($l) => $l['veiculo']->id === $veiculo->id);

        $this->assertEquals(700.0, $linha['manutencaoAvulsa']);
        $this->assertEquals(700.0, $linha['custoTotal']);
    }

    public function test_despesa_geral_com_veiculo_vira_custo_direto_e_nao_entra_no_rateio(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $dataDentro = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $veiculoA = Veiculo::factory()->create();
        $veiculoB = Veiculo::factory()->create();

        DespesaGeral::factory()->create([
            'veiculo_id'   => $veiculoA->id,
            'valor'        => 900,
            'data_despesa' => $dataDentro,
        ]);

        $response = $this->get(route('custo-frota.index'));
        $linhas = $response->viewData('linhas');

        $linhaA = $linhas->first(fn ($l) => $l['veiculo']->id === $veiculoA->id);
        $linhaB = $linhas->first(fn ($l) => $l['veiculo']->id === $veiculoB->id);

        $this->assertEquals(900.0, $linhaA['custoFixoDireto']);
        $this->assertEquals(0.0, $linhaA['overheadRateado']);
        $this->assertEquals(0.0, $linhaB['custoFixoDireto']);
        $this->assertEquals(0.0, $linhaB['overheadRateado']);
    }

    public function test_despesa_geral_sem_veiculo_e_rateada_por_dias_ativos(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $veiculoA = Veiculo::factory()->create();
        $veiculoA->forceFill(['created_at' => '2025-12-01'])->save();

        $veiculoB = Veiculo::factory()->create();
        $veiculoB->forceFill(['created_at' => '2026-01-06'])->save();

        DespesaGeral::factory()->create([
            'veiculo_id'   => null,
            'valor'        => 1500,
            'data_despesa' => '2026-01-03',
        ]);

        $response = $this->get(route('custo-frota.index', [
            'data_inicio' => '2026-01-01',
            'data_fim'    => '2026-01-10',
        ]));

        $linhas = $response->viewData('linhas');
        $linhaA = $linhas->first(fn ($l) => $l['veiculo']->id === $veiculoA->id);
        $linhaB = $linhas->first(fn ($l) => $l['veiculo']->id === $veiculoB->id);

        $this->assertEquals(10, $linhaA['diasAtivos']);
        $this->assertEquals(5, $linhaB['diasAtivos']);
        $this->assertEquals(1000.0, $linhaA['overheadRateado']);
        $this->assertEquals(500.0, $linhaB['overheadRateado']);
    }

    public function test_carreta_vinculada_a_cavalo_nao_aparece_como_linha_propria(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $cavalo  = Veiculo::factory()->create(['tipo' => 'truck']);
        $carreta = Veiculo::factory()->vinculadaA($cavalo)->create();

        $response = $this->get(route('custo-frota.index'));
        $linhas = $response->viewData('linhas');

        $this->assertNotNull($linhas->first(fn ($l) => $l['veiculo']->id === $cavalo->id));
        $this->assertNull($linhas->first(fn ($l) => $l['veiculo']->id === $carreta->id));
    }

    public function test_veiculo_sem_viagem_aparece_com_km_zero_e_custo_km_nulo(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $veiculo = Veiculo::factory()->create();

        $response = $this->get(route('custo-frota.index'));
        $linha = $response->viewData('linhas')->first(fn ($l) => $l['veiculo']->id === $veiculo->id);

        $this->assertEquals(0, $linha['kmRodados']);
        $this->assertNull($linha['custoPorKm']);
    }

    public function test_custo_total_soma_todas_as_fontes_de_custo(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $dataDentro = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $veiculoA = Veiculo::factory()->create();
        $veiculoB = Veiculo::factory()->create();

        // custo direto (viagem): combustível 300 + manutenção 50
        Viagem::factory()->encerrada()->create([
            'veiculo_id'        => $veiculoA->id,
            'data_saida'        => $dataDentro,
            'km_inicial'        => 100,
            'km_final'          => 1100,
            'valor_frete'       => 2000,
            'total_combustivel' => 300,
            'total_manutencao'  => 50,
        ]);

        // manutenção avulsa: 150
        Manutencao::factory()->create([
            'veiculo_id'      => $veiculoA->id,
            'data_manutencao' => $dataDentro,
            'valor'           => 150,
        ]);

        // custo fixo direto (seguro do veículo A): 200
        DespesaGeral::factory()->create([
            'veiculo_id'   => $veiculoA->id,
            'valor'        => 200,
            'data_despesa' => $dataDentro,
        ]);

        // overhead genuíno, sem veículo: 300 — rateado 50/50 entre A e B
        // (mesmo created_at, mesmos dias ativos no período)
        DespesaGeral::factory()->create([
            'veiculo_id'   => null,
            'valor'        => 300,
            'data_despesa' => $dataDentro,
        ]);

        $response = $this->get(route('custo-frota.index'));
        $linha = $response->viewData('linhas')->first(fn ($l) => $l['veiculo']->id === $veiculoA->id);

        // 300+50 (direto) + 150 (avulsa) + 200 (fixo direto) + 150 (overhead rateado) = 850
        $this->assertEquals(850.0, $linha['custoTotal']);
        $this->assertEquals(0.85, $linha['custoPorKm']);
        $this->assertEquals(2.0, $linha['receitaPorKm']);
        $this->assertEquals(1.15, $linha['margemPorKm']);
    }

    public function test_kpis_da_frota_sao_calculados_corretamente(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $dataDentro = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $veiculoCaro = Veiculo::factory()->create();
        $veiculoBarato = Veiculo::factory()->create();

        Viagem::factory()->encerrada()->create([
            'veiculo_id'        => $veiculoCaro->id,
            'data_saida'        => $dataDentro,
            'km_inicial'        => 100,
            'km_final'          => 1100,
            'valor_frete'       => 2000,
            'total_combustivel' => 300,
            'total_manutencao'  => 50,
        ]);
        DespesaGeral::factory()->create([
            'veiculo_id' => $veiculoCaro->id, 'valor' => 200, 'data_despesa' => $dataDentro,
        ]);

        Viagem::factory()->encerrada()->create([
            'veiculo_id'        => $veiculoBarato->id,
            'data_saida'        => $dataDentro,
            'km_inicial'        => 100,
            'km_final'          => 600,
            'valor_frete'       => 1000,
            'total_combustivel' => 100,
            'total_manutencao'  => 0,
        ]);

        // overhead 300, rateado 50/50 (mesmo created_at) => 150 pra cada
        DespesaGeral::factory()->create([
            'veiculo_id' => null, 'valor' => 300, 'data_despesa' => $dataDentro,
        ]);

        $response = $this->get(route('custo-frota.index'));

        // veiculoCaro: 300+50+200+150 = 700 / 1000km = 0.70
        // veiculoBarato: 100+0+0+150 = 250 / 500km = 0.50
        $response->assertViewHas('custoMedioFrota', round((700 + 250) / (1000 + 500), 2));
        $response->assertViewHas('custoFixoTotal', 200 + 300.0);
        $response->assertViewHas('percentualCustoFixo', round((500 / 950) * 100, 1));
        $response->assertViewHas('veiculoMaisCaro', fn ($l) => $l['veiculo']->id === $veiculoCaro->id);
        $response->assertViewHas('veiculoMaisEficiente', fn ($l) => $l['veiculo']->id === $veiculoBarato->id);
    }

    public function test_ranking_ordena_por_custo_km_desc_com_nulls_por_ultimo(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $dataDentro = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $veiculoAlto = Veiculo::factory()->create();
        $veiculoBaixo = Veiculo::factory()->create();
        $veiculoSemViagem = Veiculo::factory()->create();

        Viagem::factory()->encerrada()->create([
            'veiculo_id' => $veiculoAlto->id, 'data_saida' => $dataDentro,
            'km_inicial' => 100, 'km_final' => 200, 'total_combustivel' => 200, 'total_manutencao' => 0,
        ]);
        Viagem::factory()->encerrada()->create([
            'veiculo_id' => $veiculoBaixo->id, 'data_saida' => $dataDentro,
            'km_inicial' => 100, 'km_final' => 200, 'total_combustivel' => 50, 'total_manutencao' => 0,
        ]);

        $response = $this->get(route('custo-frota.index'));
        $ids = $response->viewData('linhas')->map(fn ($l) => $l['veiculo']->id)->all();

        $this->assertEquals([
            $veiculoAlto->id, $veiculoBaixo->id, $veiculoSemViagem->id,
        ], $ids);
    }

    public function test_tendencia_calcula_custo_e_receita_por_km_do_mes_atual(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Viagem::factory()->encerrada()->create([
            'data_saida'        => Carbon::now()->startOfMonth()->addDay()->format('Y-m-d'),
            'km_inicial'        => 100,
            'km_final'          => 300,
            'valor_frete'       => 800,
            'total_combustivel' => 100,
            'total_manutencao'  => 0,
        ]);

        $response = $this->get(route('custo-frota.index'));
        $tendencia = $response->viewData('tendencia');

        $this->assertCount(6, $tendencia);
        $mesAtual = end($tendencia);
        $this->assertEquals(0.5, $mesAtual['custoPorKm']);
        $this->assertEquals(4.0, $mesAtual['receitaPorKm']);
    }

    public function test_csv_contem_os_valores_corretos_por_veiculo(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $dataDentro = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $veiculo = Veiculo::factory()->create(['placa' => 'CSV1234']);

        Viagem::factory()->encerrada()->create([
            'veiculo_id'        => $veiculo->id,
            'data_saida'        => $dataDentro,
            'km_inicial'        => 100,
            'km_final'          => 1100,
            'valor_frete'       => 700,
            'total_combustivel' => 300,
            'total_manutencao'  => 50,
        ]);

        $response = $this->get(route('custo-frota.csv'));
        $response->assertOk();

        $conteudo = $response->streamedContent();
        $linha = collect(explode("\n", $conteudo))->first(fn ($l) => str_contains($l, 'CSV1234'));

        $this->assertNotNull($linha);
        $colunas = str_getcsv($linha, ';');

        // Veículo;KM Rodado;Custo Direto;Manutenção Avulsa;Custo Fixo Direto;Overhead Rateado;Custo Total;R$/km;Receita R$/km;Margem R$/km
        $this->assertEquals('CSV1234', $colunas[0]);
        $this->assertEquals('1.000', $colunas[1]);
        $this->assertEquals('350,00', $colunas[2]);
        $this->assertEquals('350,00', $colunas[6]);
        $this->assertEquals('0,35', $colunas[7]);
        $this->assertEquals('0,70', $colunas[8]);
        $this->assertEquals('0,35', $colunas[9]);
    }

    public function test_csv_retorna_content_type_csv(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get(route('custo-frota.csv'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
