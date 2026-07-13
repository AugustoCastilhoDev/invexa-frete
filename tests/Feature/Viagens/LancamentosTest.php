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

    public function test_registra_km_do_veiculo_no_lancamento_de_combustivel(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $this->post(route('lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 150,
            'km_veiculo'      => 45230,
            'data_lancamento' => now()->format('Y-m-d'),
        ]);

        $this->assertEquals(45230, Lancamento::firstOrFail()->km_veiculo);
    }

    public function test_registra_litros_abastecidos_e_atualiza_total_da_viagem(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $this->post(route('lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 300,
            'litros'          => 65.5,
            'data_lancamento' => now()->format('Y-m-d'),
        ]);

        $this->assertEquals(65.5, Lancamento::firstOrFail()->litros);
        $this->assertEquals(65.5, $viagem->fresh()->total_litros);
    }

    public function test_registra_valor_do_litro_no_lancamento_de_combustivel(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $this->post(route('lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 327.5,
            'litros'          => 65,
            'valor_litro'     => 5.038,
            'data_lancamento' => now()->format('Y-m-d'),
        ]);

        $this->assertEquals(5.038, Lancamento::firstOrFail()->valor_litro);
    }

    public function test_valor_do_litro_e_opcional(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $response = $this->post(route('lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 150,
            'data_lancamento' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertNull(Lancamento::firstOrFail()->valor_litro);
    }

    public function test_km_do_veiculo_e_opcional(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $response = $this->post(route('lancamentos.store', $viagem), [
            'tipo'            => 'manutencao',
            'descricao'       => 'Troca de óleo',
            'valor'           => 200,
            'data_lancamento' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertNull(Lancamento::firstOrFail()->km_veiculo);
    }

    public function test_km_do_veiculo_nao_pode_ser_negativo(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $response = $this->post(route('lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 150,
            'km_veiculo'      => -10,
            'data_lancamento' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('km_veiculo');
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

    public function test_lancamento_criado_pelo_operador_ja_entra_aprovado(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $this->post(route('lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 100,
            'data_lancamento' => now()->format('Y-m-d'),
        ]);

        $this->assertEquals('aprovado', Lancamento::firstOrFail()->status);
    }

    public function test_lancamento_pendente_nao_entra_nos_totais_ate_ser_aprovado(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['valor_frete' => 1000, 'percentual_motorista' => 10]);
        $lancamento = Lancamento::factory()->pendente()->combustivel()->create([
            'viagem_id' => $viagem->id,
            'valor'     => 300,
        ]);

        $viagem->refresh();
        $this->assertEquals(0, $viagem->total_combustivel);

        $response = $this->patch(route('lancamentos.aprovar', $lancamento));

        $response->assertRedirect(route('viagens.show', $viagem));
        $viagem->refresh();
        $this->assertEquals(300, $viagem->total_combustivel);
        $this->assertEquals('aprovado', $lancamento->fresh()->status);
    }

    public function test_rejeitar_lancamento_mantem_fora_dos_totais(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();
        $lancamento = Lancamento::factory()->pendente()->manutencao()->create([
            'viagem_id' => $viagem->id,
            'valor'     => 150,
        ]);

        $response = $this->patch(route('lancamentos.rejeitar', $lancamento));

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertEquals('rejeitado', $lancamento->fresh()->status);
        $this->assertEquals(0, $viagem->fresh()->total_manutencao);
    }

    public function test_remover_lancamento_recalcula_totais_da_viagem(): void
    {
        $this->actingAs(User::factory()->admin()->create());

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

    public function test_operador_nao_pode_excluir_lancamento(): void
    {
        $this->actingAs(User::factory()->create());

        $lancamento = Lancamento::factory()->create();

        $response = $this->delete(route('lancamentos.destroy', $lancamento));

        $response->assertForbidden();
        $this->assertDatabaseHas('lancamentos', ['id' => $lancamento->id]);
    }
}
