<?php

namespace Tests\Unit\Models;

use App\Models\Desconto;
use App\Models\Lancamento;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViagemTest extends TestCase
{
    use RefreshDatabase;

    public function test_recalcula_comissao_do_motorista_a_partir_do_percentual(): void
    {
        $viagem = Viagem::factory()->create([
            'valor_frete'          => 2000,
            'percentual_motorista' => 15,
            'valor_adiantamento'   => 0,
        ]);

        $viagem->recalcularTotais();

        $this->assertEquals(300, $viagem->valor_motorista);
    }

    public function test_soma_lancamentos_de_combustivel_e_manutencao_separadamente(): void
    {
        $viagem = Viagem::factory()->create();

        Lancamento::factory()->combustivel()->create(['viagem_id' => $viagem->id, 'valor' => 100]);
        Lancamento::factory()->combustivel()->create(['viagem_id' => $viagem->id, 'valor' => 50]);
        Lancamento::factory()->manutencao()->create(['viagem_id' => $viagem->id, 'valor' => 80]);

        $viagem->refresh();

        $this->assertEquals(150, $viagem->total_combustivel);
        $this->assertEquals(80, $viagem->total_manutencao);
    }

    public function test_soma_descontos_da_viagem(): void
    {
        $viagem = Viagem::factory()->create();

        Desconto::factory()->create(['viagem_id' => $viagem->id, 'valor' => 40]);
        Desconto::factory()->create(['viagem_id' => $viagem->id, 'valor' => 60]);

        $viagem->refresh();

        $this->assertEquals(100, $viagem->total_descontos);
    }

    public function test_saldo_do_motorista_desconta_adiantamento_quando_descontavel(): void
    {
        $viagem = Viagem::factory()->create([
            'valor_frete'               => 1000,
            'percentual_motorista'      => 10,
            'valor_adiantamento'        => 50,
            'adiantamento_descontavel'  => true,
        ]);

        $viagem->recalcularTotais();

        // valor_motorista = 100, saldo = 100 - 0 (descontos) - 50 (adiantamento)
        $this->assertEquals(50, $viagem->saldo_motorista);
    }

    public function test_saldo_do_motorista_nao_desconta_adiantamento_quando_nao_descontavel(): void
    {
        $viagem = Viagem::factory()->create([
            'valor_frete'               => 1000,
            'percentual_motorista'      => 10,
            'valor_adiantamento'        => 50,
            'adiantamento_descontavel'  => false,
        ]);

        $viagem->recalcularTotais();

        $this->assertEquals(100, $viagem->saldo_motorista);
    }

    public function test_saldo_do_motorista_considera_descontos_e_adiantamento_juntos(): void
    {
        $viagem = Viagem::factory()->create([
            'valor_frete'               => 1000,
            'percentual_motorista'      => 10,
            'valor_adiantamento'        => 20,
            'adiantamento_descontavel'  => true,
        ]);

        Desconto::factory()->create(['viagem_id' => $viagem->id, 'valor' => 30]);

        $viagem->refresh();

        // valor_motorista = 100, saldo = 100 - 30 (desconto) - 20 (adiantamento)
        $this->assertEquals(50, $viagem->saldo_motorista);
    }

    public function test_lucro_da_transportadora_subtrai_comissao_combustivel_e_manutencao(): void
    {
        $viagem = Viagem::factory()->create([
            'valor_frete'          => 1000,
            'percentual_motorista' => 10,
        ]);

        Lancamento::factory()->combustivel()->create(['viagem_id' => $viagem->id, 'valor' => 200]);
        Lancamento::factory()->manutencao()->create(['viagem_id' => $viagem->id, 'valor' => 100]);

        $viagem->refresh();

        // frete 1000 - comissao 100 - combustivel 200 - manutencao 100 = 600
        $this->assertEquals(600, $viagem->lucro_transportadora);
    }

    public function test_lucro_da_transportadora_nao_e_afetado_por_descontos_do_motorista(): void
    {
        $viagem = Viagem::factory()->create([
            'valor_frete'          => 1000,
            'percentual_motorista' => 10,
        ]);

        Desconto::factory()->create(['viagem_id' => $viagem->id, 'valor' => 300]);

        $viagem->refresh();

        // Desconto é do motorista, não deve reduzir o lucro da transportadora
        $this->assertEquals(900, $viagem->lucro_transportadora);
    }

    public function test_km_rodados_calcula_diferenca_entre_km_final_e_inicial(): void
    {
        $viagem = Viagem::factory()->create([
            'km_inicial' => 1000,
            'km_final'   => 1450,
        ]);

        $this->assertEquals(450, $viagem->km_rodados);
    }

    public function test_km_rodados_retorna_zero_quando_km_final_nao_informado(): void
    {
        $viagem = Viagem::factory()->create([
            'km_inicial' => 1000,
            'km_final'   => null,
        ]);

        $this->assertEquals(0, $viagem->km_rodados);
    }

    public function test_media_combustivel_calcula_km_por_litro(): void
    {
        $viagem = Viagem::factory()->create([
            'km_inicial' => 1000,
            'km_final'   => 1500,
        ]);
        Lancamento::factory()->combustivel()->create(['viagem_id' => $viagem->id, 'litros' => 50]);

        $this->assertEquals(10.0, $viagem->fresh()->media_combustivel);
    }

    public function test_media_combustivel_e_nula_sem_litros_registrados(): void
    {
        $viagem = Viagem::factory()->create([
            'km_inicial' => 1000,
            'km_final'   => 1500,
        ]);

        $this->assertNull($viagem->media_combustivel);
    }

    public function test_excluir_lancamento_recalcula_totais_da_viagem(): void
    {
        $viagem = Viagem::factory()->create();

        $lancamento = Lancamento::factory()->combustivel()->create(['viagem_id' => $viagem->id, 'valor' => 100]);
        $viagem->refresh();
        $this->assertEquals(100, $viagem->total_combustivel);

        $lancamento->delete();
        $viagem->refresh();

        $this->assertEquals(0, $viagem->total_combustivel);
    }

    public function test_excluir_desconto_recalcula_totais_da_viagem(): void
    {
        $viagem = Viagem::factory()->create();

        $desconto = Desconto::factory()->create(['viagem_id' => $viagem->id, 'valor' => 75]);
        $viagem->refresh();
        $this->assertEquals(75, $viagem->total_descontos);

        $desconto->delete();
        $viagem->refresh();

        $this->assertEquals(0, $viagem->total_descontos);
    }
}
