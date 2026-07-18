<?php

namespace Tests\Feature\Api;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Motorista;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_requisicao_sem_token_e_rejeitada(): void
    {
        $response = $this->getJson('/api/v1/viagens');

        $response->assertUnauthorized();
    }

    public function test_token_valido_lista_viagens_da_propria_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->admin()->create(['empresa_id' => $empresa->id]);
        Sanctum::actingAs($user);

        $viagem = Viagem::factory()->create();

        $response = $this->getJson('/api/v1/viagens');

        $response->assertOk();
        $response->assertJsonFragment(['id' => $viagem->id]);
    }

    public function test_token_nao_ve_viagens_de_outra_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $userA = User::factory()->admin()->create(['empresa_id' => $empresaA->id]);
        $userB = User::factory()->admin()->create(['empresa_id' => $empresaB->id]);

        Sanctum::actingAs($userB);
        $viagemDeB = Viagem::factory()->create();

        Sanctum::actingAs($userA);
        $response = $this->getJson('/api/v1/viagens');

        $response->assertOk();
        $response->assertJsonMissing(['id' => $viagemDeB->id]);
    }

    public function test_show_viagem_de_outra_empresa_retorna_404(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $userA = User::factory()->admin()->create(['empresa_id' => $empresaA->id]);
        $userB = User::factory()->admin()->create(['empresa_id' => $empresaB->id]);

        Sanctum::actingAs($userB);
        $viagemDeB = Viagem::factory()->create();

        Sanctum::actingAs($userA);
        $response = $this->getJson("/api/v1/viagens/{$viagemDeB->id}");

        $response->assertNotFound();
    }

    public function test_lista_motoristas_da_propria_empresa(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);
        $motorista = Motorista::factory()->create();

        $response = $this->getJson('/api/v1/motoristas');

        $response->assertOk();
        $response->assertJsonFragment(['id' => $motorista->id, 'nome' => $motorista->nome]);
    }

    public function test_lista_veiculos_da_propria_empresa(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);
        $veiculo = Veiculo::factory()->create();

        $response = $this->getJson('/api/v1/veiculos');

        $response->assertOk();
        $response->assertJsonFragment(['id' => $veiculo->id, 'placa' => $veiculo->placa]);
    }

    public function test_lista_clientes_da_propria_empresa(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);
        $cliente = Cliente::factory()->create();

        $response = $this->getJson('/api/v1/clientes');

        $response->assertOk();
        $response->assertJsonFragment(['id' => $cliente->id, 'nome' => $cliente->nome]);
    }
}
