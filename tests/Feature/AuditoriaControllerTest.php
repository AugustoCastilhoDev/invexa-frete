<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ve_a_tela_de_auditoria(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Viagem::factory()->create();

        $response = $this->get(route('auditoria.index'));

        $response->assertOk();
        $response->assertSee('Log de Auditoria');
        $response->assertSee('viagens');
    }

    public function test_operador_nao_acessa_auditoria(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('auditoria.index'));

        $response->assertForbidden();
    }

    public function test_filtra_por_log_name(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Viagem::factory()->create();

        $response = $this->get(route('auditoria.index', ['log_name' => 'viagens']));

        $response->assertOk();
        $response->assertSee('Viagens #');
        $response->assertDontSee('Clientes #');
    }
}
