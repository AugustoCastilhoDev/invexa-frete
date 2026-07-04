<?php

namespace Tests\Feature\Portal;

use App\Models\Motorista;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_motorista_com_acesso_ativo_consegue_logar_com_cpf_e_senha(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal('minha-senha')->create(['cpf' => '123.456.789-10']);

        $response = $this->post(route('portal.login'), [
            'cpf'      => '123.456.789-10',
            'password' => 'minha-senha',
        ]);

        $this->assertAuthenticatedAs($motorista, 'motorista');
        $response->assertRedirect(route('portal.viagens.index'));
    }

    public function test_login_funciona_independente_de_formatacao_do_cpf_digitado(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal('minha-senha')->create(['cpf' => '123.456.789-10']);

        $response = $this->post(route('portal.login'), [
            'cpf'      => '12345678910',
            'password' => 'minha-senha',
        ]);

        $this->assertAuthenticatedAs($motorista, 'motorista');
    }

    public function test_senha_incorreta_nao_loga(): void
    {
        Motorista::factory()->comAcessoPortal('minha-senha')->create(['cpf' => '123.456.789-10']);

        $response = $this->post(route('portal.login'), [
            'cpf'      => '123.456.789-10',
            'password' => 'senha-errada',
        ]);

        $this->assertGuest('motorista');
        $response->assertSessionHasErrors('cpf');
    }

    public function test_motorista_sem_acesso_ativado_nao_loga_mesmo_com_senha_certa(): void
    {
        $motorista = Motorista::factory()->create([
            'cpf'          => '123.456.789-10',
            'password'     => bcrypt('minha-senha'),
            'portal_ativo' => false,
        ]);

        $response = $this->post(route('portal.login'), [
            'cpf'      => '123.456.789-10',
            'password' => 'minha-senha',
        ]);

        $this->assertGuest('motorista');
        $response->assertSessionHasErrors('cpf');
    }

    public function test_visitante_e_redirecionado_para_login_do_portal_nao_do_admin(): void
    {
        $response = $this->get(route('portal.viagens.index'));

        $response->assertRedirect(route('portal.login'));
    }

    public function test_admin_autenticado_nao_acessa_rotas_do_portal(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());

        $response = $this->get(route('portal.viagens.index'));

        $response->assertRedirect(route('portal.login'));
    }

    public function test_login_nao_reaproveita_url_pretendida_deixada_por_uma_tentativa_de_acesso_admin(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal('minha-senha')->create(['cpf' => '123.456.789-10']);

        // Simula o rastro deixado por uma visita anterior (não autenticada) a uma
        // rota do painel admin, que fica gravado numa chave de sessão compartilhada.
        $this->get(route('dashboard'));

        $response = $this->post(route('portal.login'), [
            'cpf'      => '123.456.789-10',
            'password' => 'minha-senha',
        ]);

        $response->assertRedirect(route('portal.viagens.index'));
    }

    public function test_logout_encerra_a_sessao_do_motorista(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $this->actingAs($motorista, 'motorista');

        $response = $this->post(route('portal.logout'));

        $response->assertRedirect(route('portal.login'));
        $this->assertGuest('motorista');
    }
}
