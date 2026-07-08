<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\Manutencao;
use App\Models\Motorista;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_exibe_cards_de_resumo(): void
    {
        $this->actingAs(User::factory()->create());

        Viagem::factory()->create(['status' => 'aberta']);
        Viagem::factory()->encerrada()->create(['valor_frete' => 1000, 'percentual_motorista' => 10]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalViagensAbertas', 1);
    }

    public function test_faturamento_do_mes_inclui_frete_recebido_mesmo_sem_a_viagem_encerrar(): void
    {
        $this->actingAs(User::factory()->create());

        Viagem::factory()->create([
            'status'                 => 'em_andamento',
            'valor_frete'            => 1000,
            'lucro_transportadora'   => 800,
            'frete_recebido'         => true,
            'data_recebimento_frete' => now(),
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('faturamentoMes', fn ($v) => (float) $v === 1000.0);
        $response->assertViewHas('lucroMes', fn ($v) => (float) $v === 800.0);
    }

    public function test_faturamento_do_mes_nao_duplica_viagem_encerrada_e_com_frete_recebido(): void
    {
        $this->actingAs(User::factory()->create());

        Viagem::factory()->create([
            'status'                 => 'encerrada',
            'valor_frete'            => 1000,
            'lucro_transportadora'   => 800,
            'updated_at'             => now(),
            'frete_recebido'         => true,
            'data_recebimento_frete' => now(),
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('faturamentoMes', fn ($v) => (float) $v === 1000.0);
        $response->assertViewHas('lucroMes', fn ($v) => (float) $v === 800.0);
    }

    public function test_grafico_retorna_json_com_labels_e_totais(): void
    {
        $this->actingAs(User::factory()->create());

        Viagem::factory()->encerrada()->create([
            'valor_frete'          => 1000,
            'percentual_motorista' => 10,
            'updated_at'           => now(),
        ]);

        $response = $this->getJson(route('dashboard.grafico', ['tipo' => '30']));

        $response->assertOk();
        $response->assertJsonStructure(['labels', 'fretes', 'lucros', 'totais' => ['frete', 'lucro']]);
    }

    public function test_dashboard_sem_pendencias_nao_exibe_a_secao(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Pendências');
    }

    public function test_dashboard_lista_motorista_com_cnh_vencendo(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create([
            'nome'         => 'Motorista CNH Vencendo',
            'status'       => 'ativo',
            'validade_cnh' => now()->addDays(10),
        ]);

        // motorista com CNH em dia não deve aparecer
        Motorista::factory()->create(['validade_cnh' => now()->addYear()]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('cnhVencendo', function ($lista) use ($motorista) {
            return $lista->count() === 1 && $lista->first()->is($motorista);
        });
        $response->assertSee('Motorista CNH Vencendo');
    }

    public function test_dashboard_conta_cavalo_e_carreta_vinculada_como_um_unico_veiculo(): void
    {
        $this->actingAs(User::factory()->create());

        $cavalo = Veiculo::factory()->create(['tipo' => 'truck']);
        Veiculo::factory()->vinculadaA($cavalo)->create();
        Veiculo::factory()->create(); // veículo avulso, conta normalmente

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalVeiculosAtivos', 2);
    }

    public function test_dashboard_lista_veiculo_em_manutencao(): void
    {
        $this->actingAs(User::factory()->create());

        Veiculo::factory()->emManutencao()->create(['placa' => 'MNT1A23']);
        Veiculo::factory()->create(); // ativo, não deve aparecer

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('veiculosEmManutencao', fn ($lista) => $lista->count() === 1);
        $response->assertSee('MNT1A23');
    }

    public function test_dashboard_lista_documento_pendente(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();
        Documento::factory()->create(['viagem_id' => $viagem->id, 'status' => 'pendente', 'numero' => '000123']);
        Documento::factory()->create(['viagem_id' => $viagem->id, 'status' => 'autorizado']);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('documentosPendentes', fn ($lista) => $lista->count() === 1);
        $response->assertSee('000123');
    }

    public function test_dashboard_lista_manutencao_preventiva_vencendo(): void
    {
        $this->actingAs(User::factory()->create());

        $veiculo = Veiculo::factory()->create(['placa' => 'PRV9Z99']);
        Manutencao::factory()->create([
            'veiculo_id' => $veiculo->id,
            'proxima_manutencao_data' => now()->addDays(15),
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('manutencoesVencendo', fn ($lista) => $lista->count() === 1);
        $response->assertSee('PRV9Z99');
    }
}
