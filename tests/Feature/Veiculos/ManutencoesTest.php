<?php

namespace Tests\Feature\Veiculos;

use App\Models\Manutencao;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManutencoesTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_manutencao_em_andamento_coloca_veiculo_em_manutencao(): void
    {
        $this->actingAs(User::factory()->create());
        $veiculo = Veiculo::factory()->create(['status' => 'ativo']);

        $response = $this->post(route('manutencoes.store', $veiculo), [
            'tipo'             => 'corretiva',
            'descricao'        => 'Troca de embreagem',
            'data_manutencao'  => now()->format('Y-m-d'),
            'valor'            => 1500,
            'status'           => 'em_andamento',
        ]);

        $response->assertRedirect(route('veiculos.show', $veiculo));
        $this->assertDatabaseHas('manutencoes', ['descricao' => 'Troca de embreagem']);
        $this->assertEquals('manutencao', $veiculo->fresh()->status);
    }

    public function test_store_exige_campos_obrigatorios(): void
    {
        $this->actingAs(User::factory()->create());
        $veiculo = Veiculo::factory()->create();

        $response = $this->post(route('manutencoes.store', $veiculo), []);

        $response->assertSessionHasErrors(['tipo', 'descricao', 'data_manutencao', 'valor', 'status']);
    }

    public function test_marcar_manutencao_como_concluida_reativa_o_veiculo(): void
    {
        $this->actingAs(User::factory()->create());
        $veiculo    = Veiculo::factory()->create(['status' => 'ativo']);
        $manutencao = Manutencao::factory()->emAndamento()->create(['veiculo_id' => $veiculo->id]);

        $response = $this->patch(route('manutencoes.update', $manutencao), [
            'status' => 'concluida',
        ]);

        $response->assertRedirect(route('veiculos.show', $veiculo));
        $this->assertEquals('concluida', $manutencao->fresh()->status);
        $this->assertEquals('ativo', $veiculo->fresh()->status);
    }

    public function test_excluir_manutencao_remove_registro(): void
    {
        $this->actingAs(User::factory()->create());
        $manutencao = Manutencao::factory()->create();

        $response = $this->delete(route('manutencoes.destroy', $manutencao));

        $response->assertRedirect(route('veiculos.show', $manutencao->veiculo));
        $this->assertDatabaseMissing('manutencoes', ['id' => $manutencao->id]);
    }

    public function test_veiculo_show_exibe_historico_de_manutencoes(): void
    {
        $this->actingAs(User::factory()->create());
        $veiculo = Veiculo::factory()->create();
        Manutencao::factory()->create(['veiculo_id' => $veiculo->id, 'descricao' => 'Revisão geral']);

        $response = $this->get(route('veiculos.show', $veiculo));

        $response->assertOk();
        $response->assertSee('Revisão geral');
    }
}
