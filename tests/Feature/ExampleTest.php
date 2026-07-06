<?php

namespace Tests\Feature;

use App\Models\Motorista;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_ve_a_landing_page_na_raiz(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('landing');
    }

    public function test_usuario_autenticado_e_redirecionado_da_raiz_para_o_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_motorista_autenticado_e_redirecionado_da_raiz_para_o_portal(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $this->actingAs($motorista, 'motorista');

        $response = $this->get('/');

        $response->assertRedirect(route('portal.viagens.index'));
    }

    public function test_dashboard_exige_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}
