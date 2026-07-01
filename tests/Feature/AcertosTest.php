<?php

namespace Tests\Feature;

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
}
