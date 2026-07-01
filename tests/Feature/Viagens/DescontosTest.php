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
        $this->actingAs(User::factory()->create());

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
}
