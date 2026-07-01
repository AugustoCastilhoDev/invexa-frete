<?php

namespace Tests\Feature;

use App\Models\Motorista;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_tela_de_viagem_exibe_quem_abriu_e_quem_lancou_o_desconto(): void
    {
        $abridor = User::factory()->create(['name' => 'Ana Aberturas']);
        $this->actingAs($abridor);
        $viagem = Viagem::factory()->create();

        $lancador = User::factory()->create(['name' => 'Beto Lançamentos']);
        $this->actingAs($lancador);
        $this->post(route('descontos.store', $viagem), [
            'tipo'          => 'vale',
            'descricao'     => 'Vale',
            'valor'         => 10,
            'data_desconto' => now()->format('Y-m-d'),
        ]);

        $response = $this->get(route('viagens.show', $viagem));

        $response->assertOk();
        $response->assertSee('Ana Aberturas');
        $response->assertSee('Beto Lançamentos');
    }

    public function test_tela_de_motorista_exibe_quem_cadastrou(): void
    {
        $user = User::factory()->create(['name' => 'Carla Cadastros']);
        $this->actingAs($user);

        $motorista = Motorista::factory()->create();

        $response = $this->get(route('motoristas.show', $motorista));

        $response->assertOk();
        $response->assertSee('Carla Cadastros');
    }
}
