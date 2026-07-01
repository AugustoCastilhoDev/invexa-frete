<?php

namespace Tests\Feature;

use App\Models\User;
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
}
