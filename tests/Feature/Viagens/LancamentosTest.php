<?php

namespace Tests\Feature\Viagens;

use App\Models\Lancamento;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LancamentosTest extends TestCase
{
    use RefreshDatabase;

    public function test_adicionar_lancamento_de_combustivel_atualiza_totais_da_viagem(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['valor_frete' => 1000, 'percentual_motorista' => 10]);

        $response = $this->post(route('lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 150,
            'data_lancamento' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));

        $viagem->refresh();
        $this->assertEquals(150, $viagem->total_combustivel);
        $this->assertEquals(750, $viagem->lucro_transportadora);
    }

    public function test_store_exige_tipo_valido(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $response = $this->post(route('lancamentos.store', $viagem), [
            'tipo'            => 'invalido',
            'descricao'       => 'Teste',
            'valor'           => 10,
            'data_lancamento' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('tipo');
    }

    public function test_remover_lancamento_recalcula_totais_da_viagem(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem     = Viagem::factory()->create(['valor_frete' => 1000, 'percentual_motorista' => 10]);
        $lancamento = Lancamento::factory()->manutencao()->create(['viagem_id' => $viagem->id, 'valor' => 200]);

        $viagem->refresh();
        $this->assertEquals(200, $viagem->total_manutencao);

        $response = $this->delete(route('lancamentos.destroy', $lancamento));

        $response->assertRedirect(route('viagens.show', $viagem));

        $viagem->refresh();
        $this->assertEquals(0, $viagem->total_manutencao);
        $this->assertDatabaseMissing('lancamentos', ['id' => $lancamento->id]);
    }
}
