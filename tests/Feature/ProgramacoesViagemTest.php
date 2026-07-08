<?php

namespace Tests\Feature;

use App\Models\Motorista;
use App\Models\ProgramacaoViagem;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramacoesViagemTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('programacoes.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_cadastra_programacao_de_viagem(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create();
        $veiculo   = Veiculo::factory()->create();

        $response = $this->post(route('programacoes.store'), [
            'motorista_id'  => $motorista->id,
            'veiculo_id'    => $veiculo->id,
            'origem'        => 'São Paulo',
            'destino'       => 'Curitiba',
            'data_prevista' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('programacoes.index'));
        $this->assertDatabaseHas('programacoes_viagem', [
            'motorista_id' => $motorista->id,
            'veiculo_id'   => $veiculo->id,
            'origem'       => 'São Paulo',
            'destino'      => 'Curitiba',
            'status'       => 'pendente',
        ]);
    }

    public function test_nao_permite_duas_programacoes_pendentes_para_o_mesmo_veiculo(): void
    {
        $this->actingAs(User::factory()->create());

        $veiculo = Veiculo::factory()->create();
        ProgramacaoViagem::factory()->create(['veiculo_id' => $veiculo->id]);

        $response = $this->post(route('programacoes.store'), [
            'motorista_id'  => Motorista::factory()->create()->id,
            'veiculo_id'    => $veiculo->id,
            'origem'        => 'São Paulo',
            'destino'       => 'Curitiba',
            'data_prevista' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('veiculo_id');
        $this->assertEquals(1, ProgramacaoViagem::count());
    }

    public function test_nao_permite_editar_programacao_ja_confirmada(): void
    {
        $this->actingAs(User::factory()->create());

        $programacao = ProgramacaoViagem::factory()->confirmada()->create();

        $response = $this->get(route('programacoes.edit', $programacao));

        $response->assertStatus(400);
    }

    public function test_veiculos_sem_programacao_nao_conta_carreta_ja_coberta_pelo_cavalo(): void
    {
        $this->actingAs(User::factory()->create());

        $cavalo  = Veiculo::factory()->create();
        $carreta = Veiculo::factory()->vinculadaA($cavalo)->create();
        ProgramacaoViagem::factory()->create(['veiculo_id' => $cavalo->id]);

        $response = $this->get(route('programacoes.index'));

        $response->assertOk();
        $response->assertViewHas('totalVeiculosSemProgramacao', 0);
    }

    public function test_index_lista_apenas_pendentes_por_padrao(): void
    {
        $this->actingAs(User::factory()->create());

        ProgramacaoViagem::factory()->create(['origem' => 'Pendente Origem']);
        ProgramacaoViagem::factory()->confirmada()->create(['origem' => 'Confirmada Origem']);

        $response = $this->get(route('programacoes.index'));

        $response->assertOk();
        $response->assertSee('Pendente Origem');
        $response->assertDontSee('Confirmada Origem');
    }

    public function test_atualiza_programacao_pendente(): void
    {
        $this->actingAs(User::factory()->create());

        $programacao = ProgramacaoViagem::factory()->create(['destino' => 'Antigo Destino']);

        $response = $this->put(route('programacoes.update', $programacao), [
            'motorista_id'  => $programacao->motorista_id,
            'veiculo_id'    => $programacao->veiculo_id,
            'origem'        => $programacao->origem,
            'destino'       => 'Novo Destino',
            'data_prevista' => $programacao->data_prevista->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('programacoes.index'));
        $this->assertEquals('Novo Destino', $programacao->fresh()->destino);
    }

    public function test_remove_programacao(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $programacao = ProgramacaoViagem::factory()->create();

        $response = $this->delete(route('programacoes.destroy', $programacao));

        $response->assertRedirect(route('programacoes.index'));
        $this->assertSoftDeleted($programacao);
    }

    public function test_operador_nao_pode_remover_programacao(): void
    {
        $this->actingAs(User::factory()->create());

        $programacao = ProgramacaoViagem::factory()->create();

        $response = $this->delete(route('programacoes.destroy', $programacao));

        $response->assertForbidden();
    }

    public function test_cadastra_programacao_com_valor_de_frete_opcional(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create();
        $veiculo   = Veiculo::factory()->create();

        $response = $this->post(route('programacoes.store'), [
            'motorista_id'  => $motorista->id,
            'veiculo_id'    => $veiculo->id,
            'origem'        => 'São Paulo',
            'destino'       => 'Curitiba',
            'valor_frete'   => 3500,
            'data_prevista' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('programacoes.index'));
        $this->assertDatabaseHas('programacoes_viagem', [
            'motorista_id' => $motorista->id,
            'veiculo_id'   => $veiculo->id,
            'valor_frete'  => 3500,
        ]);
    }

    public function test_cadastra_programacao_sem_valor_de_frete(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('programacoes.store'), [
            'motorista_id'  => Motorista::factory()->create()->id,
            'veiculo_id'    => Veiculo::factory()->create()->id,
            'origem'        => 'São Paulo',
            'destino'       => 'Curitiba',
            'data_prevista' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('programacoes.index'));
        $this->assertNull(ProgramacaoViagem::firstOrFail()->valor_frete);
    }

    public function test_confirmar_programacao_cria_viagem_e_marca_como_confirmada(): void
    {
        $this->actingAs(User::factory()->create());

        $programacao = ProgramacaoViagem::factory()->create([
            'origem'  => 'Recife',
            'destino' => 'Salvador',
        ]);

        $response = $this->post(route('viagens.store'), [
            'programacao_id'        => $programacao->id,
            'motorista_id'          => $programacao->motorista_id,
            'veiculo_id'            => $programacao->veiculo_id,
            'origem'                => $programacao->origem,
            'destino'               => $programacao->destino,
            'data_saida'            => now()->format('Y-m-d'),
            'valor_frete'           => 5000,
            'percentual_motorista'  => 10,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('viagens', [
            'motorista_id' => $programacao->motorista_id,
            'veiculo_id'   => $programacao->veiculo_id,
            'origem'       => 'Recife',
            'destino'      => 'Salvador',
        ]);

        $programacao->refresh();
        $this->assertEquals('confirmada', $programacao->status);
        $this->assertNotNull($programacao->viagem_id);
    }
}
