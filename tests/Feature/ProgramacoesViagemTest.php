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

    public function test_index_expoe_lista_de_veiculos_sem_programacao_para_o_drill_down(): void
    {
        $this->actingAs(User::factory()->create());

        $veiculoSemProgramacao = Veiculo::factory()->create(['placa' => 'SEM1P23']);
        $veiculoComProgramacao = Veiculo::factory()->create();
        ProgramacaoViagem::factory()->create(['veiculo_id' => $veiculoComProgramacao->id]);

        $response = $this->get(route('programacoes.index'));

        $response->assertOk();
        $response->assertViewHas('veiculosSemProgramacao', function ($lista) use ($veiculoSemProgramacao) {
            return $lista->count() === 1 && $lista->first()->is($veiculoSemProgramacao);
        });
        $response->assertSee('SEM1P23');
    }

    public function test_filtra_por_risco_de_no_show(): void
    {
        \Illuminate\Support\Carbon::setTestNow('2026-08-10 06:00:00');
        $this->actingAs(User::factory()->create());

        $emRisco = ProgramacaoViagem::factory()->create([
            'data_prevista' => '2026-08-10',
            'hora_coleta'   => '07:00',
        ]);
        $foraDeRisco = ProgramacaoViagem::factory()->create([
            'data_prevista' => '2026-08-10',
            'hora_coleta'   => '11:00',
        ]);

        $response = $this->get(route('programacoes.index', ['risco_no_show' => 1]));

        $response->assertOk();
        $response->assertViewHas('programacoes', function ($lista) use ($emRisco, $foraDeRisco) {
            return $lista->total() === 1 && $lista->first()->is($emRisco) && ! $lista->contains($foraDeRisco);
        });

        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_filtra_por_periodo_hoje(): void
    {
        \Illuminate\Support\Carbon::setTestNow('2026-08-10 06:00:00');
        $this->actingAs(User::factory()->create());

        $hoje    = ProgramacaoViagem::factory()->create(['data_prevista' => '2026-08-10']);
        $amanha  = ProgramacaoViagem::factory()->create(['data_prevista' => '2026-08-11']);

        $response = $this->get(route('programacoes.index', ['status' => 'todas', 'periodo' => 'hoje']));

        $response->assertOk();
        $response->assertViewHas('programacoes', function ($lista) use ($hoje, $amanha) {
            return $lista->total() === 1 && $lista->first()->is($hoje) && ! $lista->contains($amanha);
        });

        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_filtra_por_periodo_esta_semana(): void
    {
        \Illuminate\Support\Carbon::setTestNow('2026-08-10 06:00:00'); // segunda-feira
        $this->actingAs(User::factory()->create());

        $dentroDaSemana = ProgramacaoViagem::factory()->create(['data_prevista' => '2026-08-13']);
        $foraDaSemana   = ProgramacaoViagem::factory()->create(['data_prevista' => '2026-08-20']);

        $response = $this->get(route('programacoes.index', ['status' => 'todas', 'periodo' => 'semana']));

        $response->assertOk();
        $response->assertViewHas('programacoes', function ($lista) use ($dentroDaSemana, $foraDaSemana) {
            return $lista->contains($dentroDaSemana) && ! $lista->contains($foraDaSemana);
        });

        \Illuminate\Support\Carbon::setTestNow();
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

    public function test_cadastra_programacao_com_hora_de_coleta_e_entrega_prevista(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('programacoes.store'), [
            'motorista_id'  => Motorista::factory()->create()->id,
            'veiculo_id'    => Veiculo::factory()->create()->id,
            'origem'        => 'São Paulo',
            'destino'       => 'Curitiba',
            'data_prevista' => now()->addDays(2)->format('Y-m-d'),
            'hora_coleta'   => '08:30',
            'data_entrega_prevista' => now()->addDays(3)->format('Y-m-d'),
            'hora_entrega_prevista' => '17:00',
        ]);

        $response->assertRedirect(route('programacoes.index'));
        $programacao = ProgramacaoViagem::firstOrFail();
        // Formato bruto da coluna TIME varia entre MySQL (produção, normaliza pra
        // "H:i:s") e SQLite (suíte de testes, guarda exatamente o que foi salvo)
        // — comparar pelo valor semântico (H:i) em vez da string crua.
        $this->assertSame('08:30', \Illuminate\Support\Carbon::parse($programacao->hora_coleta)->format('H:i'));
        $this->assertSame('17:00', \Illuminate\Support\Carbon::parse($programacao->hora_entrega_prevista)->format('H:i'));
        $this->assertSame(now()->addDays(3)->format('Y-m-d'), $programacao->data_entrega_prevista->format('Y-m-d'));
    }

    public function test_operador_marca_chegada_no_local_de_coleta(): void
    {
        $this->actingAs(User::factory()->create());

        $programacao = ProgramacaoViagem::factory()->create([
            'data_prevista' => now()->format('Y-m-d'),
            'hora_coleta'   => '08:00',
        ]);

        $response = $this->post(route('programacoes.chegada', $programacao), ['horario' => '08:15']);

        $response->assertRedirect(route('programacoes.index'));
        $programacao->refresh();
        $this->assertTrue($programacao->chegadaInformada());
        $this->assertSame('08:15', $programacao->chegada_horario_informado->format('H:i'));
        $this->assertNotNull($programacao->chegada_informada_em);
    }

    public function test_nao_pode_marcar_chegada_em_programacao_ja_confirmada(): void
    {
        $this->actingAs(User::factory()->create());

        $programacao = ProgramacaoViagem::factory()->confirmada()->create(['hora_coleta' => '08:00']);

        $response = $this->post(route('programacoes.chegada', $programacao), ['horario' => '08:15']);

        $response->assertStatus(400);
    }

    public function test_risco_de_no_show_considera_coleta_prevista_dentro_de_2h_sem_chegada(): void
    {
        \Illuminate\Support\Carbon::setTestNow('2026-08-10 06:00:00');
        $this->actingAs(User::factory()->create());

        // Coleta prevista daqui a 1h: dentro da janela de risco (≤2h) e sem chegada informada.
        $emRisco = ProgramacaoViagem::factory()->create([
            'data_prevista' => '2026-08-10',
            'hora_coleta'   => '07:00',
        ]);

        // Coleta prevista daqui a 5h: fora da janela, não deveria contar.
        ProgramacaoViagem::factory()->create([
            'data_prevista' => '2026-08-10',
            'hora_coleta'   => '11:00',
        ]);

        $resultado = ProgramacaoViagem::emRiscoDeNoShow();

        $this->assertCount(1, $resultado);
        $this->assertTrue($resultado->first()->is($emRisco));

        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_risco_de_no_show_nao_conta_quando_chegada_ja_informada(): void
    {
        $this->actingAs(User::factory()->create());

        $programacao = ProgramacaoViagem::factory()->create([
            'data_prevista' => now()->format('Y-m-d'),
            'hora_coleta'   => now()->addMinutes(30)->format('H:i'),
        ]);
        $programacao->marcarChegada(now()->format('H:i'));

        $this->assertCount(0, ProgramacaoViagem::emRiscoDeNoShow());
    }

    public function test_risco_de_no_show_nao_conta_sem_hora_de_coleta_definida(): void
    {
        $this->actingAs(User::factory()->create());

        ProgramacaoViagem::factory()->create([
            'data_prevista' => now()->format('Y-m-d'),
            'hora_coleta'   => null,
        ]);

        $this->assertCount(0, ProgramacaoViagem::emRiscoDeNoShow());
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

    public function test_cadastra_programacao_com_carreta(): void
    {
        $this->actingAs(User::factory()->create());

        $cavalo  = Veiculo::factory()->cavalo()->create();
        $carreta = Veiculo::factory()->carreta()->create();

        $response = $this->post(route('programacoes.store'), [
            'motorista_id'  => Motorista::factory()->create()->id,
            'veiculo_id'    => $cavalo->id,
            'carreta_id'    => $carreta->id,
            'origem'        => 'São Paulo',
            'destino'       => 'Curitiba',
            'data_prevista' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('programacoes.index'));
        $this->assertDatabaseHas('programacoes_viagem', ['veiculo_id' => $cavalo->id, 'carreta_id' => $carreta->id]);
    }

    // O link "Confirmar" (ver programacoes/index.blade.php) repassa carreta_id
    // via query string pro formulário de nova viagem — sem isso a escolha da
    // carreta feita na programação se perderia ao virar viagem de verdade.
    public function test_confirmar_programacao_com_carreta_leva_a_escolha_para_a_viagem(): void
    {
        $this->actingAs(User::factory()->create());

        $cavalo  = Veiculo::factory()->cavalo()->create();
        $carreta = Veiculo::factory()->carreta()->create();
        $programacao = ProgramacaoViagem::factory()->create([
            'veiculo_id' => $cavalo->id,
            'carreta_id' => $carreta->id,
        ]);

        $response = $this->post(route('viagens.store'), [
            'programacao_id'       => $programacao->id,
            'motorista_id'         => $programacao->motorista_id,
            'veiculo_id'           => $programacao->veiculo_id,
            'carreta_id'           => $programacao->carreta_id,
            'origem'               => $programacao->origem,
            'destino'              => $programacao->destino,
            'data_saida'           => now()->format('Y-m-d'),
            'valor_frete'          => 5000,
            'percentual_motorista' => 10,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('viagens', ['veiculo_id' => $cavalo->id, 'carreta_id' => $carreta->id]);
    }
}
