<?php

namespace Tests\Feature\Empresas;

use App\Models\Empresa;
use App\Models\Motorista;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmpresaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('empresas.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_comum_nao_pode_acessar_gestao_de_empresas(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get(route('empresas.index'));

        $response->assertForbidden();
    }

    public function test_super_admin_pode_listar_empresas(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        Empresa::factory()->create(['nome' => 'Transportadora Teste']);

        $response = $this->get(route('empresas.index'));

        $response->assertOk();
        $response->assertSee('Transportadora Teste');
    }

    public function test_listagem_exibe_badge_de_atraso_para_empresa_inadimplente(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        Empresa::factory()->create(['nome' => 'Transportadora Inadimplente', 'asaas_status' => 'PAYMENT_OVERDUE']);
        Empresa::factory()->create(['nome' => 'Transportadora Em Dia', 'asaas_status' => 'PAYMENT_RECEIVED']);

        $response = $this->get(route('empresas.index'));

        $response->assertOk();
        $response->assertSee('Atrasado');
        $response->assertSee('Em dia');
    }

    public function test_super_admin_pode_criar_empresa_com_admin_inicial(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $response = $this->post(route('empresas.store'), [
            'nome'                          => 'Transportadora Nova',
            'cnpj'                          => '11.222.333/0001-44',
            'plano'                         => 'starter',
            'ciclo_cobranca'                => 'mensal',
            'admin_name'                    => 'Admin da Nova',
            'admin_email'                   => 'admin@nova.com',
            'admin_password'                => 'senha12345',
            'admin_password_confirmation'   => 'senha12345',
        ]);

        $response->assertRedirect(route('empresas.index'));

        $empresa = Empresa::where('nome', 'Transportadora Nova')->firstOrFail();
        $this->assertDatabaseHas('users', [
            'email'      => 'admin@nova.com',
            'role'       => 'admin',
            'empresa_id' => $empresa->id,
        ]);
        $this->assertSame('11.222.333/0001-44', $empresa->fresh()->cnpj);
    }

    // cnpj é cifrado (IV aleatório por gravação); a unicidade passa a
    // depender do hash determinístico — mesmo com formatação diferente do
    // já cadastrado, precisa ser barrado.
    public function test_nao_permite_cnpj_duplicado(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        Empresa::factory()->create(['cnpj' => '11.222.333/0001-44']);

        $response = $this->post(route('empresas.store'), [
            'nome'                          => 'Transportadora Duplicada',
            'cnpj'                          => '11222333000144',
            'plano'                         => 'starter',
            'ciclo_cobranca'                => 'mensal',
            'admin_name'                    => 'Admin Duplicada',
            'admin_email'                   => 'admin@duplicada.com',
            'admin_password'                => 'senha12345',
            'admin_password_confirmation'   => 'senha12345',
        ]);

        $response->assertSessionHasErrors('cnpj');
        $this->assertDatabaseMissing('empresas', ['nome' => 'Transportadora Duplicada']);
    }

    // Duas empresas sem CNPJ não podem ser tratadas como "duplicata" uma da
    // outra — nullable continua permitindo múltiplos nulos, como antes.
    public function test_permite_criar_mais_de_uma_empresa_sem_cnpj(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        Empresa::factory()->create(['cnpj' => null]);

        $response = $this->post(route('empresas.store'), [
            'nome'                          => 'Transportadora Sem CNPJ',
            'plano'                         => 'starter',
            'ciclo_cobranca'                => 'mensal',
            'admin_name'                    => 'Admin Sem CNPJ',
            'admin_email'                   => 'admin@semcnpj.com',
            'admin_password'                => 'senha12345',
            'admin_password_confirmation'   => 'senha12345',
        ]);

        $response->assertRedirect(route('empresas.index'));
        $this->assertDatabaseHas('empresas', ['nome' => 'Transportadora Sem CNPJ']);
    }

    public function test_super_admin_pode_visualizar_detalhes_de_uma_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create(['nome' => 'Transportadora Detalhada']);
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id, 'name' => 'Admin da Detalhada']);
        Motorista::factory()->create(['empresa_id' => $empresa->id]);

        $response = $this->get(route('empresas.show', $empresa));

        $response->assertOk();
        $response->assertSee('Transportadora Detalhada');
        $response->assertSee('Admin da Detalhada');
        $response->assertViewHas('resumo', fn ($resumo) => $resumo['motoristas'] === 1);
    }

    public function test_admin_comum_nao_pode_visualizar_detalhes_de_empresa(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $empresa = Empresa::factory()->create();

        $response = $this->get(route('empresas.show', $empresa));

        $response->assertForbidden();
    }

    public function test_super_admin_pode_editar_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create(['nome' => 'Nome Antigo']);

        $response = $this->put(route('empresas.update', $empresa), [
            'nome' => 'Nome Novo',
            'cnpj' => $empresa->cnpj,
        ]);

        $response->assertRedirect(route('empresas.index'));
        $this->assertEquals('Nome Novo', $empresa->fresh()->nome);
    }

    public function test_super_admin_pode_definir_limite_de_veiculos_ao_criar_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->post(route('empresas.store'), [
            'nome'                        => 'Transportadora Pequena',
            'limite_veiculos'             => 5,
            'plano'                       => 'starter',
            'ciclo_cobranca'              => 'mensal',
            'admin_name'                  => 'Admin Pequena',
            'admin_email'                 => 'admin@pequena.com',
            'admin_password'              => 'senha12345',
            'admin_password_confirmation' => 'senha12345',
        ]);

        $this->assertDatabaseHas('empresas', ['nome' => 'Transportadora Pequena', 'limite_veiculos' => 5]);
    }

    public function test_super_admin_pode_alterar_limite_de_veiculos_de_uma_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create(['limite_veiculos' => 5]);

        $this->put(route('empresas.update', $empresa), [
            'nome'            => $empresa->nome,
            'cnpj'            => $empresa->cnpj,
            'limite_veiculos' => 10,
        ]);

        $this->assertEquals(10, $empresa->fresh()->limite_veiculos);
    }

    public function test_super_admin_pode_desativar_e_reativar_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create();

        $this->patch(route('empresas.toggle-status', $empresa));
        $this->assertEquals('inativo', $empresa->fresh()->status);

        $this->patch(route('empresas.toggle-status', $empresa));
        $this->assertEquals('ativo', $empresa->fresh()->status);
    }

    public function test_cadastro_de_empresa_sem_plano_e_rejeitado(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $response = $this->post(route('empresas.store'), [
            'nome'                        => 'Transportadora Sem Plano',
            'admin_name'                  => 'Admin Sem Plano',
            'admin_email'                 => 'admin@semplano.com',
            'admin_password'              => 'senha12345',
            'admin_password_confirmation' => 'senha12345',
        ]);

        $response->assertSessionHasErrors('plano');
    }

    public function test_criacao_de_empresa_com_plano_cria_assinatura_no_asaas(): void
    {
        config(['services.asaas.api_key' => 'chave-de-teste']);
        Http::fake([
            '*/customers' => Http::response(['id' => 'cus_123'], 200),
            '*/subscriptions' => Http::response(['id' => 'sub_456'], 200),
        ]);

        $this->actingAs(User::factory()->superAdmin()->create());

        $this->post(route('empresas.store'), [
            'nome'                        => 'Transportadora Asaas',
            'plano'                       => 'pro',
            'ciclo_cobranca'              => 'mensal',
            'admin_name'                  => 'Admin Asaas',
            'admin_email'                 => 'admin@asaas.com',
            'admin_password'              => 'senha12345',
            'admin_password_confirmation' => 'senha12345',
        ]);

        $empresa = Empresa::where('nome', 'Transportadora Asaas')->firstOrFail();
        $this->assertSame('cus_123', $empresa->asaas_customer_id);
        $this->assertSame('sub_456', $empresa->asaas_subscription_id);
        $this->assertSame('em_trial', $empresa->asaas_status);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/subscriptions')
                && $request['cycle'] === 'MONTHLY'
                && $request['value'] === 1339.00;
        });
    }

    public function test_plano_enterprise_nao_cria_assinatura_no_asaas(): void
    {
        config(['services.asaas.api_key' => 'chave-de-teste']);
        Http::fake();

        $this->actingAs(User::factory()->superAdmin()->create());

        $this->post(route('empresas.store'), [
            'nome'                        => 'Transportadora Enterprise',
            'plano'                       => 'enterprise',
            'admin_name'                  => 'Admin Enterprise',
            'admin_email'                 => 'admin@enterprise.com',
            'admin_password'              => 'senha12345',
            'admin_password_confirmation' => 'senha12345',
        ]);

        $empresa = Empresa::where('nome', 'Transportadora Enterprise')->firstOrFail();
        $this->assertNull($empresa->asaas_customer_id);
        Http::assertNothingSent();
    }

    public function test_falha_na_api_do_asaas_nao_impede_cadastro_da_empresa(): void
    {
        config(['services.asaas.api_key' => 'chave-de-teste']);
        Http::fake(['*/customers' => Http::response(['errors' => []], 400)]);

        $this->actingAs(User::factory()->superAdmin()->create());

        $response = $this->post(route('empresas.store'), [
            'nome'                        => 'Transportadora Resiliente',
            'plano'                       => 'starter',
            'ciclo_cobranca'              => 'mensal',
            'admin_name'                  => 'Admin Resiliente',
            'admin_email'                 => 'admin@resiliente.com',
            'admin_password'              => 'senha12345',
            'admin_password_confirmation' => 'senha12345',
        ]);

        $response->assertRedirect(route('empresas.index'));
        $empresa = Empresa::where('nome', 'Transportadora Resiliente')->firstOrFail();
        $this->assertNull($empresa->asaas_customer_id);
    }

    public function test_sem_chave_da_api_configurada_cadastro_continua_funcionando(): void
    {
        config(['services.asaas.api_key' => null]);

        $this->actingAs(User::factory()->superAdmin()->create());

        $response = $this->post(route('empresas.store'), [
            'nome'                        => 'Transportadora Sem Chave',
            'plano'                       => 'starter',
            'ciclo_cobranca'              => 'mensal',
            'admin_name'                  => 'Admin Sem Chave',
            'admin_email'                 => 'admin@semchave.com',
            'admin_password'              => 'senha12345',
            'admin_password_confirmation' => 'senha12345',
        ]);

        $response->assertRedirect(route('empresas.index'));
        $this->assertDatabaseHas('empresas', ['nome' => 'Transportadora Sem Chave']);
    }

    public function test_super_admin_cria_assinatura_retroativa_para_empresa_sem_asaas(): void
    {
        config(['services.asaas.api_key' => 'chave-de-teste']);
        Http::fake([
            '*/customers' => Http::response(['id' => 'cus_retro'], 200),
            '*/subscriptions' => Http::response(['id' => 'sub_retro'], 200),
        ]);

        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create(['plano' => null, 'asaas_subscription_id' => null]);
        User::factory()->admin()->create(['empresa_id' => $empresa->id]);

        $response = $this->post(route('empresas.assinatura.criar', $empresa), [
            'plano' => 'business',
            'ciclo_cobranca' => 'anual',
        ]);

        $response->assertRedirect(route('empresas.show', $empresa));
        $empresa->refresh();
        $this->assertSame('business', $empresa->plano);
        $this->assertSame('anual', $empresa->ciclo_cobranca);
        $this->assertSame('cus_retro', $empresa->asaas_customer_id);
        $this->assertSame('sub_retro', $empresa->asaas_subscription_id);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/subscriptions')
            && $request['cycle'] === 'YEARLY'
            && $request['value'] === 22490.00);
    }

    public function test_definir_plano_enterprise_na_assinatura_retroativa_nao_chama_asaas(): void
    {
        Http::fake();
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create(['plano' => null]);
        User::factory()->admin()->create(['empresa_id' => $empresa->id]);

        $this->post(route('empresas.assinatura.criar', $empresa), ['plano' => 'enterprise']);

        $empresa->refresh();
        $this->assertSame('enterprise', $empresa->plano);
        $this->assertNull($empresa->asaas_subscription_id);
        Http::assertNothingSent();
    }

    public function test_criar_assinatura_sem_admin_ativo_falha_com_erro_claro(): void
    {
        config(['services.asaas.api_key' => 'chave-de-teste']);
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create(['plano' => null]);

        $response = $this->post(route('empresas.assinatura.criar', $empresa), [
            'plano' => 'starter',
            'ciclo_cobranca' => 'mensal',
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_comum_nao_pode_criar_assinatura_de_empresa(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $empresa = Empresa::factory()->create();

        $response = $this->post(route('empresas.assinatura.criar', $empresa), [
            'plano' => 'starter',
            'ciclo_cobranca' => 'mensal',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_de_empresa_desativada_nao_consegue_logar(): void
    {
        $empresa = Empresa::factory()->inativa()->create();
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id]);

        $response = $this->post('/login', [
            'email'    => $admin->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
