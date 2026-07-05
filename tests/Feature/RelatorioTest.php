<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RelatorioTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('relatorios.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_totaliza_apenas_viagens_do_periodo_e_status_selecionados(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $dataDentro = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        $dentro = Viagem::factory()->encerrada()->create([
            'data_saida'  => $dataDentro,
            'valor_frete' => 1500,
        ]);

        // fora do período
        Viagem::factory()->encerrada()->create([
            'data_saida' => Carbon::now()->subMonths(2)->format('Y-m-d'),
        ]);

        // dentro do período mas status diferente do filtro padrão (encerrada)
        Viagem::factory()->create([
            'status'     => 'aberta',
            'data_saida' => $dataDentro,
        ]);

        $response = $this->get(route('relatorios.index'));

        $response->assertOk();
        $response->assertViewHas('totais', function ($totais) use ($dentro) {
            return $totais['total_viagens'] === 1
                && (float) $totais['frete'] === (float) $dentro->valor_frete;
        });
    }

    public function test_pdf_gera_documento_do_relatorio(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Viagem::factory()->encerrada()->create(['data_saida' => Carbon::now()->format('Y-m-d')]);

        $response = $this->get(route('relatorios.pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_csv_gera_arquivo_com_as_viagens_do_periodo(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $viagem = Viagem::factory()->encerrada()->create([
            'data_saida'  => Carbon::now()->format('Y-m-d'),
            'valor_frete' => 1234.56,
        ]);

        $response = $this->get(route('relatorios.csv'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $conteudo = $response->streamedContent();
        $this->assertStringContainsString($viagem->motorista->nome, $conteudo);
        $this->assertStringContainsString('1234,56', $conteudo);
    }

    public function test_operador_nao_pode_acessar_relatorios(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('relatorios.index'));

        $response->assertForbidden();
    }
}
