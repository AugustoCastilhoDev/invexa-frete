<?php

namespace Tests\Feature\Viagens;

use App\Models\Desconto;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DescontosTest extends TestCase
{
    use RefreshDatabase;

    public function test_adicionar_desconto_atualiza_saldo_do_motorista(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create([
            'valor_frete'          => 1000,
            'percentual_motorista' => 10,
            'valor_adiantamento'   => 0,
        ]);

        $response = $this->post(route('descontos.store', $viagem), [
            'tipo'          => 'vale',
            'descricao'     => 'Vale alimentação',
            'valor'         => 40,
            'data_desconto' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));

        $viagem->refresh();
        $this->assertEquals(40, $viagem->total_descontos);
        $this->assertEquals(60, $viagem->saldo_motorista);
    }

    public function test_adicionar_bonificacao_aumenta_saldo_do_motorista_sem_entrar_nos_descontos(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create([
            'valor_frete'          => 1000,
            'percentual_motorista' => 10,
            'valor_adiantamento'   => 0,
        ]);

        $response = $this->post(route('descontos.store', $viagem), [
            'tipo'          => 'bonificacao',
            'descricao'     => 'Diária extra',
            'valor'         => 50,
            'data_desconto' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));

        $viagem->refresh();
        $this->assertEquals(0, $viagem->total_descontos);
        $this->assertEquals(50, $viagem->total_bonificacoes);
        $this->assertEquals(150, $viagem->saldo_motorista);
    }

    public function test_bonificacao_e_desconto_juntos_calculam_saldo_corretamente(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create([
            'valor_frete'          => 1000,
            'percentual_motorista' => 10,
            'valor_adiantamento'   => 0,
        ]);

        Desconto::factory()->create(['viagem_id' => $viagem->id, 'tipo' => 'vale', 'valor' => 30]);
        Desconto::factory()->create(['viagem_id' => $viagem->id, 'tipo' => 'bonificacao', 'valor' => 80]);

        $viagem->refresh();
        // 100 (comissão) - 30 (desconto) + 80 (bonificação) = 150
        $this->assertEquals(30, $viagem->total_descontos);
        $this->assertEquals(80, $viagem->total_bonificacoes);
        $this->assertEquals(150, $viagem->saldo_motorista);
    }

    public function test_remover_bonificacao_recalcula_saldo_do_motorista(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $viagem      = Viagem::factory()->create(['valor_frete' => 1000, 'percentual_motorista' => 10]);
        $bonificacao = Desconto::factory()->create(['viagem_id' => $viagem->id, 'tipo' => 'bonificacao', 'valor' => 50]);

        $viagem->refresh();
        $this->assertEquals(150, $viagem->saldo_motorista);

        $response = $this->delete(route('descontos.destroy', $bonificacao));

        $response->assertRedirect(route('viagens.show', $viagem));

        $viagem->refresh();
        $this->assertEquals(100, $viagem->saldo_motorista);
        $this->assertEquals(0, $viagem->total_bonificacoes);
    }

    public function test_store_exige_tipo_valido(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $response = $this->post(route('descontos.store', $viagem), [
            'tipo'          => 'invalido',
            'descricao'     => 'Teste',
            'valor'         => 10,
            'data_desconto' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('tipo');
    }

    public function test_remover_desconto_recalcula_saldo_do_motorista(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $viagem   = Viagem::factory()->create(['valor_frete' => 1000, 'percentual_motorista' => 10]);
        $desconto = Desconto::factory()->create(['viagem_id' => $viagem->id, 'valor' => 30]);

        $viagem->refresh();
        $this->assertEquals(70, $viagem->saldo_motorista);

        $response = $this->delete(route('descontos.destroy', $desconto));

        $response->assertRedirect(route('viagens.show', $viagem));

        $viagem->refresh();
        $this->assertEquals(100, $viagem->saldo_motorista);
        $this->assertDatabaseMissing('descontos', ['id' => $desconto->id]);
    }

    public function test_operador_nao_pode_excluir_desconto(): void
    {
        $this->actingAs(User::factory()->create());

        $desconto = Desconto::factory()->create();

        $response = $this->delete(route('descontos.destroy', $desconto));

        $response->assertForbidden();
        $this->assertDatabaseHas('descontos', ['id' => $desconto->id]);
    }
}
