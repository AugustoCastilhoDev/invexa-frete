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

    public function test_csv_retorna_content_type_csv(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get(route('custo-frota.csv'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
