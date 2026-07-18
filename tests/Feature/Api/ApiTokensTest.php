<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokensTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('api-tokens.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_usuario_pode_gerar_um_token(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post(route('api-tokens.store'), [
            'name' => 'Integração ERP',
        ]);

        $response->assertRedirect(route('api-tokens.index'));
        $response->assertSessionHas('token_gerado');
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'Integração ERP',
        ]);
    }

    public function test_usuario_so_revoga_o_proprio_token(): void
    {
        $userA = User::factory()->admin()->create();
        $userB = User::factory()->admin()->create();

        $tokenDeB = $userB->createToken('Token do B');

        $this->actingAs($userA)->delete(route('api-tokens.destroy', $tokenDeB->accessToken->id));

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $tokenDeB->accessToken->id]);
    }

    public function test_usuario_revoga_o_proprio_token(): void
    {
        $user = User::factory()->admin()->create();
        $token = $user->createToken('Token a revogar');

        $this->actingAs($user)->delete(route('api-tokens.destroy', $token->accessToken->id));

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }
}
