<?php

namespace Tests\Feature\Viagens;

use App\Models\Cliente;
use App\Models\Motorista;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViagemLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('viagens.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_usuario_autenticado_pode_abrir_uma_viagem_com_calculo_automatico(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create();
        $veiculo   = Veiculo::factory()->create();
        $cliente   = Cliente::factory()->create();

        $response = $this->post(route('viagens.store'), [
            'motorista_id'         => $motorista->id,
            'veiculo_id'           => $veiculo->id,
            'cliente_id'           => $cliente->id,
            'origem'               => 'Curitiba',
            'destino'              => 'São Paulo',
            'data_saida'           => now()->format('Y-m-d'),
            'km_inicial'           => 1000,
            'valor_frete'          => 2000,
            'percentual_motorista' => 10,
            'valor_adiantamento'   => 0,
        ]);

        $viagem = Viagem::firstOrFail();

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertEquals('aberta', $viagem->status);
        $this->assertEquals(200, $viagem->valor_motorista);
        $this->assertEquals(1800, $viagem->lucro_transportadora);
    }

    public function test_abrir_viagem_com_adiantamento_nao_descontavel_nao_reduz_saldo_de_imediato(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create();
        $veiculo   = Veiculo::factory()->create();

        // "adiantamento_descontavel" não é enviado, simulando o checkbox desmarcado.
        $response = $this->post(route('viagens.store'), [
            'motorista_id'         => $motorista->id,
            'veiculo_id'           => $veiculo->id,
            'origem'               => 'Curitiba',
            'destino'              => 'São Paulo',
            'data_saida'           => now()->format('Y-m-d'),
            'valor_frete'          => 1000,
            'percentual_motorista' => 10,
            'valor_adiantamento'   => 50,
        ]);

        $viagem = Viagem::firstOrFail();

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertFalse($viagem->adiantamento_descontavel);
        // valor_motorista = 100; saldo não deve descontar o adiantamento de 50
        $this->assertEquals(100, $viagem->saldo_motorista);
    }

    public function test_abrir_viagem_com_adiantamento_descontavel_reduz_saldo_de_imediato(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create();
        $veiculo   = Veiculo::factory()->create();

        $response = $this->post(route('viagens.store'), [
            'motorista_id'              => $motorista->id,
            'veiculo_id'                => $veiculo->id,
            'origem'                    => 'Curitiba',
            'destino'                   => 'São Paulo',
            'data_saida'                => now()->format('Y-m-d'),
            'valor_frete'               => 1000,
            'percentual_motorista'      => 10,
            'valor_adiantamento'        => 50,
            'adiantamento_descontavel'  => '1',
        ]);

        $viagem = Viagem::firstOrFail();

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertTrue($viagem->adiantamento_descontavel);
        // valor_motorista = 100; saldo = 100 - 50
        $this->assertEquals(50, $viagem->saldo_motorista);
    }

    public function test_store_exige_campos_obrigatorios(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('viagens.store'), []);

        $response->assertSessionHasErrors([
            'motorista_id', 'veiculo_id', 'origem', 'destino', 'data_saida', 'valor_frete', 'percentual_motorista',
        ]);
    }

    public function test_atualizar_viagem_recalcula_totais(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create([
            'valor_frete'          => 1000,
            'percentual_motorista' => 10,
        ]);

        $response = $this->put(route('viagens.update', $viagem), [
            'motorista_id'         => $viagem->motorista_id,
            'veiculo_id'           => $viagem->veiculo_id,
            'cliente_id'           => $viagem->cliente_id,
            'origem'               => $viagem->origem,
            'destino'              => $viagem->destino,
            'data_saida'           => $viagem->data_saida->format('Y-m-d'),
            'valor_frete'          => 1000,
            'percentual_motorista' => 20,
            'valor_adiantamento'   => 0,
            'status'               => 'aberta',
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));

        $viagem->refresh();
        $this->assertEquals(200, $viagem->valor_motorista);
        $this->assertEquals(800, $viagem->lucro_transportadora);
    }

    public function test_encerrar_viagem_define_status_e_data_retorno(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'aguardando_acerto']);

        $response = $this->patch(route('viagens.encerrar', $viagem));

        $response->assertRedirect(route('viagens.show', $viagem));

        $viagem->refresh();
        $this->assertEquals('encerrada', $viagem->status);
        $this->assertNotNull($viagem->data_retorno);
    }

    public function test_avancar_status_de_aberta_move_para_em_andamento(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'aberta']);

        $response = $this->patch(route('viagens.avancar-status', $viagem));

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertEquals('em_andamento', $viagem->fresh()->status);
    }

    public function test_avancar_status_de_em_andamento_move_para_aguardando_acerto(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'em_andamento']);

        $response = $this->patch(route('viagens.avancar-status', $viagem));

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertEquals('aguardando_acerto', $viagem->fresh()->status);
    }

    public function test_avancar_status_nao_pula_para_encerrada(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'aguardando_acerto']);

        $response = $this->patch(route('viagens.avancar-status', $viagem));

        $response->assertStatus(400);
        $this->assertEquals('aguardando_acerto', $viagem->fresh()->status);
    }

    public function test_avancar_status_de_viagem_encerrada_e_rejeitado(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->encerrada()->create();

        $response = $this->patch(route('viagens.avancar-status', $viagem));

        $response->assertStatus(400);
    }

    public function test_excluir_viagem_remove_da_listagem_padrao(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $viagem = Viagem::factory()->create();

        $response = $this->delete(route('viagens.destroy', $viagem));

        $response->assertRedirect(route('viagens.index'));
        $this->assertSoftDeleted($viagem);
    }

    public function test_operador_nao_pode_excluir_viagem(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $response = $this->delete(route('viagens.destroy', $viagem));

        $response->assertForbidden();
        $this->assertNotSoftDeleted($viagem);
    }

    public function test_imprimir_gera_pdf_da_viagem(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $response = $this->get(route('viagens.imprimir', $viagem));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
