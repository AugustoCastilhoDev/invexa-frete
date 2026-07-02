<?php

namespace Tests\Unit\Models;

use App\Models\Manutencao;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManutencaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_criar_manutencao_em_andamento_coloca_veiculo_em_manutencao(): void
    {
        $veiculo = Veiculo::factory()->create(['status' => 'ativo']);

        Manutencao::factory()->emAndamento()->create(['veiculo_id' => $veiculo->id]);

        $this->assertEquals('manutencao', $veiculo->fresh()->status);
    }

    public function test_concluir_manutencao_volta_veiculo_para_ativo(): void
    {
        $veiculo = Veiculo::factory()->create(['status' => 'ativo']);
        $manutencao = Manutencao::factory()->emAndamento()->create(['veiculo_id' => $veiculo->id]);

        $manutencao->update(['status' => 'concluida']);

        $this->assertEquals('ativo', $veiculo->fresh()->status);
    }

    public function test_concluir_uma_manutencao_nao_reativa_veiculo_se_outra_ainda_esta_em_andamento(): void
    {
        $veiculo = Veiculo::factory()->create(['status' => 'ativo']);
        $primeira  = Manutencao::factory()->emAndamento()->create(['veiculo_id' => $veiculo->id]);
        Manutencao::factory()->emAndamento()->create(['veiculo_id' => $veiculo->id]);

        $primeira->update(['status' => 'concluida']);

        $this->assertEquals('manutencao', $veiculo->fresh()->status);
    }

    public function test_excluir_manutencao_em_andamento_reativa_veiculo(): void
    {
        $veiculo = Veiculo::factory()->create(['status' => 'ativo']);
        $manutencao = Manutencao::factory()->emAndamento()->create(['veiculo_id' => $veiculo->id]);

        $manutencao->delete();

        $this->assertEquals('ativo', $veiculo->fresh()->status);
    }

    public function test_criar_manutencao_ja_concluida_nao_altera_veiculo_ativo(): void
    {
        $veiculo = Veiculo::factory()->create(['status' => 'ativo']);

        Manutencao::factory()->create(['veiculo_id' => $veiculo->id, 'status' => 'concluida']);

        $this->assertEquals('ativo', $veiculo->fresh()->status);
    }

    public function test_scope_proximas_vencendo_considera_apenas_o_registro_mais_recente_por_veiculo(): void
    {
        $veiculo = Veiculo::factory()->create();

        Manutencao::factory()->create([
            'veiculo_id' => $veiculo->id,
            'data_manutencao' => now()->subMonths(2),
            'proxima_manutencao_data' => now()->subDays(5), // já teria vencido, mas foi superada
        ]);

        $maisRecente = Manutencao::factory()->create([
            'veiculo_id' => $veiculo->id,
            'data_manutencao' => now(),
            'proxima_manutencao_data' => now()->addDays(200), // fora da janela de 30 dias
        ]);

        $resultado = Manutencao::proximasVencendo(30)->get();

        $this->assertCount(0, $resultado);
    }

    public function test_scope_proximas_vencendo_inclui_registro_dentro_da_janela(): void
    {
        $veiculo = Veiculo::factory()->create();

        Manutencao::factory()->create([
            'veiculo_id' => $veiculo->id,
            'proxima_manutencao_data' => now()->addDays(10),
        ]);

        $this->assertCount(1, Manutencao::proximasVencendo(30)->get());
    }
}
