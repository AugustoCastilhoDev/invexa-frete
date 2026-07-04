<?php

namespace Tests\Feature\Portal;

use App\Models\Motorista;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalSenhaTest extends TestCase
{
    use RefreshDatabase;

    public function test_motorista_troca_a_propria_senha(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal('senha-antiga')->create();

        $response = $this->actingAs($motorista, 'motorista')->put(route('portal.senha.update'), [
            'senha_atual'           => 'senha-antiga',
            'password'              => 'senha-nova-123',
            'password_confirmation' => 'senha-nova-123',
        ]);

        $response->assertRedirect(route('portal.senha.edit'));
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('senha-nova-123', $motorista->fresh()->password));
    }

    public function test_exige_senha_atual_correta(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal('senha-antiga')->create();

        $response = $this->actingAs($motorista, 'motorista')->put(route('portal.senha.update'), [
            'senha_atual'           => 'senha-errada',
            'password'              => 'senha-nova-123',
            'password_confirmation' => 'senha-nova-123',
        ]);

        $response->assertSessionHasErrors('senha_atual');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('senha-antiga', $motorista->fresh()->password));
    }
}
