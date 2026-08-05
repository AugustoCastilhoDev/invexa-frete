<?php

namespace Tests\Feature\Portal;

use App\Models\Motorista;
use App\Models\ProgramacaoViagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalProgramacoesTest extends TestCase
{
    use RefreshDatabase;

    public function test_motorista_ve_propria_programacao_pendente_na_listagem(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $programacao = ProgramacaoViagem::factory()->create([
            'motorista_id' => $motorista->id,
            'origem'       => 'Curitiba',
            'destino'      => 'Joinville',
        ]);

        $response = $this->actingAs($motorista, 'motorista')->get(route('portal.viagens.index'));

        $response->assertOk();
        $response->assertViewHas('programacoesPendentes', function ($programacoes) use ($programacao) {
            return $programacoes->count() === 1 && $programacoes->first()->is($programacao);
        });
    }

    public function test_motorista_informa_chegada_no_local_de_coleta(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $programacao = ProgramacaoViagem::factory()->create([
            'motorista_id'  => $motorista->id,
            'data_prevista' => now()->format('Y-m-d'),
            'hora_coleta'   => '08:00',
        ]);

        $response = $this->actingAs($motorista, 'motorista')
            ->post(route('portal.programacoes.chegada', $programacao), ['horario' => '07:55']);

        $response->assertRedirect(route('portal.viagens.index'));
        $programacao->refresh();
        $this->assertTrue($programacao->chegadaInformada());
        $this->assertSame('07:55', $programacao->chegada_horario_informado->format('H:i'));
    }

    public function test_motorista_nao_pode_informar_chegada_de_programacao_de_outro_motorista(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $outroMotorista = Motorista::factory()->comAcessoPortal()->create();
        $programacaoDeOutro = ProgramacaoViagem::factory()->create(['motorista_id' => $outroMotorista->id]);

        $response = $this->actingAs($motorista, 'motorista')
            ->post(route('portal.programacoes.chegada', $programacaoDeOutro), ['horario' => '08:00']);

        $response->assertForbidden();
        $this->assertFalse($programacaoDeOutro->fresh()->chegadaInformada());
    }

    public function test_nao_pode_informar_chegada_de_programacao_ja_confirmada(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $programacao = ProgramacaoViagem::factory()->confirmada()->create(['motorista_id' => $motorista->id]);

        $response = $this->actingAs($motorista, 'motorista')
            ->post(route('portal.programacoes.chegada', $programacao), ['horario' => '08:00']);

        $response->assertStatus(400);
    }

    // Se o motorista só conseguir enviar depois de sair do local (sem sinal),
    // o horário que ele informou continua sendo o que vale pro cálculo de
    // no-show — não o momento em que a requisição de fato chegou no servidor.
    public function test_chegada_usa_horario_informado_pelo_motorista_nao_o_momento_do_envio(): void
    {
        \Illuminate\Support\Carbon::setTestNow('2026-08-10 22:00:00');

        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $programacao = ProgramacaoViagem::factory()->create([
            'motorista_id'  => $motorista->id,
            'data_prevista' => '2026-08-10',
            'hora_coleta'   => '08:00',
        ]);

        // Chegou às 08:05 mas só consegue enviar à noite (sem sinal no local durante o dia).
        $this->actingAs($motorista, 'motorista')
            ->post(route('portal.programacoes.chegada', $programacao), ['horario' => '08:05']);

        $programacao->refresh();
        $this->assertSame('2026-08-10 08:05:00', $programacao->chegada_horario_informado->format('Y-m-d H:i:s'));
        $this->assertSame('2026-08-10 22:00:00', $programacao->chegada_informada_em->format('Y-m-d H:i:s'));

        \Illuminate\Support\Carbon::setTestNow();
    }
}
