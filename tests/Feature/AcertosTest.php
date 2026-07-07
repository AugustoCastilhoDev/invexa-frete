<?php

namespace Tests\Feature;

use App\Models\Lancamento;
use App\Models\Motorista;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AcertosTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_sem_motorista_selecionado_nao_calcula_totais(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('acertos.index'));

        $response->assertOk();
        $response->assertViewHas('totais', []);
    }

    public function test_index_agrega_saldo_a_pagar_e_saldo_pago_por_status(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create();
        $dataViagem = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $aberta = Viagem::factory()->create([
            'motorista_id' => $motorista->id,
            'status'       => 'aberta',
            'data_saida'   => $dataViagem,
            'valor_frete'  => 1000,
            'percentual_motorista' => 10,
        ]);

        $encerrada = Viagem::factory()->encerrada()->create([
            'motorista_id' => $motorista->id,
            'data_saida'   => $dataViagem,
            'valor_frete'  => 2000,
            'percentual_motorista' => 10,
        ]);

        // fora do período do mês corrente: não deve entrar nos totais
        Viagem::factory()->create([
            'motorista_id' => $motorista->id,
            'data_saida'   => Carbon::now()->subMonths(2)->format('Y-m-d'),
        ]);

        $response = $this->get(route('acertos.index', ['motorista_id' => $motorista->id]));

        $response->assertOk();
        $response->assertViewHas('totais', function ($totais) use ($aberta, $encerrada) {
            return $totais['total_viagens'] === 2
                && (float) $totais['saldo_a_pagar'] === (float) $aberta->saldo_motorista
                && (float) $totais['saldo_pago'] === (float) $encerrada->saldo_motorista;
        });
    }

    public function test_index_calcula_media_de_combustivel_do_periodo(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista  = Motorista::factory()->create();
        $dataViagem = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $viagem1 = Viagem::factory()->create([
            'motorista_id' => $motorista->id,
            'data_saida'   => $dataViagem,
            'km_inicial'   => 1000,
            'km_final'     => 1400,
        ]);
        Lancamento::factory()->combustivel()->create(['viagem_id' => $viagem1->id, 'litros' => 50]);

        $viagem2 = Viagem::factory()->create([
            'motorista_id' => $motorista->id,
            'data_saida'   => $dataViagem,
            'km_inicial'   => 2000,
            'km_final'     => 2600,
        ]);
        Lancamento::factory()->combustivel()->create(['viagem_id' => $viagem2->id, 'litros' => 50]);

        $response = $this->get(route('acertos.index', ['motorista_id' => $motorista->id]));

        $response->assertOk();
        $response->assertViewHas('totais', function ($totais) {
            return (float) $totais['total_km'] === 1000.0
                && (float) $totais['total_litros'] === 100.0
                && (float) $totais['media_combustivel'] === 10.0;
        });
    }

    public function test_index_sem_litros_registrados_nao_calcula_media(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create();
        Viagem::factory()->create([
            'motorista_id' => $motorista->id,
            'data_saida'   => Carbon::now()->format('Y-m-d'),
            'km_inicial'   => 1000,
            'km_final'     => 1400,
        ]);

        $response = $this->get(route('acertos.index', ['motorista_id' => $motorista->id]));

        $response->assertOk();
        $response->assertViewHas('totais', fn ($totais) => $totais['media_combustivel'] === null);
    }

    public function test_pdf_gera_documento_do_motorista(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create();
        Viagem::factory()->create([
            'motorista_id' => $motorista->id,
            'data_saida'   => Carbon::now()->format('Y-m-d'),
        ]);

        $response = $this->get(route('acertos.pdf', ['motorista_id' => $motorista->id]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_csv_gera_arquivo_com_as_viagens_do_motorista(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create();
        $viagem = Viagem::factory()->create([
            'motorista_id' => $motorista->id,
            'data_saida'   => Carbon::now()->format('Y-m-d'),
            'origem'       => 'Curitiba',
        ]);

        $response = $this->get(route('acertos.csv', ['motorista_id' => $motorista->id]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Curitiba', $response->streamedContent());
    }
}
