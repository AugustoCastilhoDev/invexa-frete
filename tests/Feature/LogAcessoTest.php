<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\LogAcesso;
use App\Models\Motorista;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogAcessoTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_no_painel_registra_log_de_acesso(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->admin()->create(['empresa_id' => $empresa->id]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseCount('logs_acesso', 1);

        $log = LogAcesso::first();
        $this->assertEquals(User::class, $log->autenticavel_type);
        $this->assertEquals($user->id, $log->autenticavel_id);
        $this->assertEquals('web', $log->guard);
        $this->assertEquals($empresa->id, $log->empresa_id);
        $this->assertNotNull($log->ip);
    }

    public function test_login_no_portal_do_motorista_registra_log_de_acesso(): void
    {
        // Sem empresa própria de propósito: Motorista tem escopo global por
        // empresa, e a consulta de login roda antes de qualquer autenticação
        // — precisa cair na mesma empresa "forçada" pelo TestCase para o
        // motorista ser encontrado, igual ao padrão do PortalAuthTest.
        $motorista = Motorista::factory()->comAcessoPortal('minha-senha')->create([
            'cpf' => '123.456.789-10',
        ]);

        $this->post(route('portal.login'), [
            'cpf' => '123.456.789-10',
            'password' => 'minha-senha',
        ]);

        $this->assertAuthenticatedAs($motorista, 'motorista');

        $this->assertDatabaseCount('logs_acesso', 1);

        $log = LogAcesso::first();
        $this->assertEquals(Motorista::class, $log->autenticavel_type);
        $this->assertEquals($motorista->id, $log->autenticavel_id);
        $this->assertEquals('motorista', $log->guard);
        $this->assertEquals($motorista->empresa_id, $log->empresa_id);
    }

    public function test_login_do_super_admin_registra_log_sem_empresa(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->post('/login', [
            'email' => $superAdmin->email,
            'password' => 'password',
        ]);

        $log = LogAcesso::first();
        $this->assertNull($log->empresa_id);
    }

    public function test_tentativa_de_login_invalida_nao_gera_log(): void
    {
        $user = User::factory()->admin()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ]);

        $this->assertDatabaseCount('logs_acesso', 0);
    }
}
