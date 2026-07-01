<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('users.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_operador_nao_pode_acessar_gestao_de_usuarios(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_admin_pode_listar_usuarios(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        User::factory()->create(['name' => 'Fulano de Tal']);

        $response = $this->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('Fulano de Tal');
    }

    public function test_admin_pode_criar_usuario(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->post(route('users.store'), [
            'name'                  => 'Novo Operador',
            'email'                 => 'novo@invexa.com',
            'password'              => 'senha12345',
            'password_confirmation' => 'senha12345',
            'role'                  => 'operador',
            'status'                => 'ativo',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'novo@invexa.com', 'role' => 'operador']);
    }

    public function test_admin_pode_editar_outro_usuario(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $user = User::factory()->create(['name' => 'Nome Antigo']);

        $response = $this->put(route('users.update', $user), [
            'name'   => 'Nome Novo',
            'email'  => $user->email,
            'role'   => 'admin',
            'status' => 'ativo',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertEquals('Nome Novo', $user->fresh()->name);
        $this->assertEquals('admin', $user->fresh()->role);
    }

    public function test_admin_nao_pode_remover_seu_proprio_papel_de_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->put(route('users.update', $admin), [
            'name'   => $admin->name,
            'email'  => $admin->email,
            'role'   => 'operador',
            'status' => 'ativo',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertEquals('admin', $admin->fresh()->role);
    }

    public function test_nao_pode_rebaixar_o_ultimo_admin_ativo(): void
    {
        $unicoAdmin = User::factory()->admin()->create();
        $outroAdmin = User::factory()->admin()->create();
        $this->actingAs($outroAdmin);

        // desativa o "outroAdmin" primeiro, deixando $unicoAdmin como último admin ativo
        $outroAdmin->update(['status' => 'inativo']);

        $response = $this->put(route('users.update', $unicoAdmin), [
            'name'   => $unicoAdmin->name,
            'email'  => $unicoAdmin->email,
            'role'   => 'operador',
            'status' => 'ativo',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertEquals('admin', $unicoAdmin->fresh()->role);
    }

    public function test_admin_nao_pode_desativar_a_si_mesmo(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->delete(route('users.destroy', $admin));

        $response->assertRedirect();
        $this->assertNotSoftDeleted($admin);
    }

    public function test_admin_pode_desativar_outro_usuario(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $user = User::factory()->create();

        $response = $this->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $this->assertSoftDeleted($user);
    }

    public function test_usuario_inativo_nao_consegue_fazer_login(): void
    {
        $user = User::factory()->inativo()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
