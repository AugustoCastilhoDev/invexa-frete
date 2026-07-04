<?php

namespace Tests\Feature\Motoristas;

use App\Models\Motorista;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotoristaPortalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ativa_acesso_ao_portal_e_define_senha(): void
    {
        $this->actingAs(User::factory()->create());
        $motorista = Motorista::factory()->create();

        $response = $this->post(route('motoristas.portal.store', $motorista), [
            'password'              => 'nova-senha-123',
            'password_confirmation' => 'nova-senha-123',
        ]);

        $response->assertRedirect(route('motoristas.edit', $motorista));
        $motorista->refresh();
        $this->assertTrue($motorista->portal_ativo);
        $this->assertNotNull($motorista->password);
    }

    public function test_exige_confirmacao_de_senha_igual(): void
    {
        $this->actingAs(User::factory()->create());
        $motorista = Motorista::factory()->create();

        $response = $this->post(route('motoristas.portal.store', $motorista), [
            'password'              => 'nova-senha-123',
            'password_confirmation' => 'outra-coisa',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertFalse($motorista->fresh()->portal_ativo);
    }

    public function test_admin_desativa_acesso_ao_portal(): void
    {
        $this->actingAs(User::factory()->create());
        $motorista = Motorista::factory()->comAcessoPortal()->create();

        $response = $this->delete(route('motoristas.portal.destroy', $motorista));

        $response->assertRedirect(route('motoristas.edit', $motorista));
        $this->assertFalse($motorista->fresh()->portal_ativo);
    }
}
