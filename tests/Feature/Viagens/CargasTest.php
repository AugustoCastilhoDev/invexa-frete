<?php

namespace Tests\Feature\Viagens;

use App\Models\Carga;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Empresa;
use App\Models\EmissaoFiscal;
use App\Models\ProgramacaoViagem;
use App\Models\Unidade;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CargasTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_carga_vinculada_a_um_cliente(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();
        $cliente = Cliente::factory()->create();

        $response = $this->post(route('cargas.store', $viagem), [
            'cliente_id' => $cliente->id,
            'valor_frete' => 850.50,
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertDatabaseHas('cargas', [
            'viagem_id' => $viagem->id,
            'cliente_id' => $cliente->id,
            'valor_frete' => 850.50,
        ]);
    }

    public function test_cria_carga_sem_valor_frete(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();
        $cliente = Cliente::factory()->create();

        $response = $this->post(route('cargas.store', $viagem), [
            'cliente_id' => $cliente->id,
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertDatabaseHas('cargas', [
            'viagem_id' => $viagem->id,
            'cliente_id' => $cliente->id,
            'valor_frete' => null,
        ]);
    }

    public function test_cria_carga_com_unidade_explicita(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();
        $cliente = Cliente::factory()->create();
        $unidade = Unidade::factory()->create();

        $response = $this->post(route('cargas.store', $viagem), [
            'cliente_id' => $cliente->id,
            'unidade_id' => $unidade->id,
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertDatabaseHas('cargas', [
            'viagem_id' => $viagem->id,
            'unidade_id' => $unidade->id,
        ]);
    }

    public function test_cria_carga_com_destino_proprio(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create(['destino' => 'São Paulo', 'destino_uf' => 'SP']);
        $cliente = Cliente::factory()->create();

        $response = $this->post(route('cargas.store', $viagem), [
            'cliente_id'               => $cliente->id,
            'destino'                  => 'Campinas',
            'destino_uf'               => 'SP',
            'destino_codigo_municipio' => '3509502',
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertDatabaseHas('cargas', [
            'viagem_id'                => $viagem->id,
            'destino'                  => 'Campinas',
            'destino_uf'               => 'SP',
            'destino_codigo_municipio' => '3509502',
        ]);
    }

    public function test_carga_sem_destino_proprio_usa_o_destino_da_viagem(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create([
            'destino'                  => 'São Paulo',
            'destino_uf'               => 'SP',
            'destino_codigo_municipio' => '3550308',
        ]);
        $carga = Carga::factory()->create(['viagem_id' => $viagem->id]);

        $this->assertSame('São Paulo', $carga->destino_efetivo);
        $this->assertSame('SP', $carga->destino_uf_efetivo);
        $this->assertSame('3550308', $carga->destino_codigo_municipio_efetivo);
    }

    public function test_carga_com_destino_proprio_nao_usa_o_da_viagem(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create(['destino' => 'São Paulo', 'destino_uf' => 'SP']);
        $carga = Carga::factory()->create([
            'viagem_id'                => $viagem->id,
            'destino'                  => 'Campinas',
            'destino_uf'               => 'SP',
            'destino_codigo_municipio' => '3509502',
        ]);

        $this->assertSame('Campinas', $carga->destino_efetivo);
        $this->assertSame('3509502', $carga->destino_codigo_municipio_efetivo);
    }

    public function test_viagem_mostra_entregas_pendentes_da_programacao_confirmada(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();
        $programacao = ProgramacaoViagem::factory()->confirmada()->create(['viagem_id' => $viagem->id]);
        $entrega = $programacao->paradas()->create(['tipo' => 'entrega', 'cidade' => 'Maceió', 'uf' => 'AL', 'valor_frete' => 800]);

        $pendentes = $viagem->fresh()->entregasPendentes();

        $this->assertCount(1, $pendentes);
        $this->assertTrue($pendentes->first()->is($entrega));
    }

    public function test_viagem_lista_coletas_planejadas_da_programacao_confirmada(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();
        $programacao = ProgramacaoViagem::factory()->confirmada()->create(['viagem_id' => $viagem->id]);
        $coleta = $programacao->paradas()->create(['tipo' => 'coleta', 'cidade' => 'Curitiba', 'uf' => 'PR']);

        $planejadas = $viagem->fresh()->coletasPlanejadas();

        $this->assertCount(1, $planejadas);
        $this->assertTrue($planejadas->first()->is($coleta));
    }

    public function test_tela_da_viagem_renderiza_sugestao_de_carga_pendente(): void
    {
        $empresa = Empresa::findOrFail(\App\Support\TenantContext::id());
        $empresa->update(['focus_nfe_ativo' => true, 'focus_nfe_ambiente' => 'homologacao', 'focus_nfe_token' => 'token-teste']);
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();
        $programacao = ProgramacaoViagem::factory()->confirmada()->create(['viagem_id' => $viagem->id]);
        $programacao->paradas()->create(['tipo' => 'entrega', 'cidade' => 'Maceió', 'uf' => 'AL', 'valor_frete' => 800]);
        $programacao->paradas()->create(['tipo' => 'coleta', 'cidade' => 'Curitiba', 'uf' => 'PR']);

        $response = $this->get(route('viagens.show', $viagem));

        $response->assertOk();
        $response->assertSee('Maceió/AL');
        $response->assertSee('Criar Carga');
        $response->assertSee('Curitiba/PR');
    }

    public function test_criar_carga_a_partir_de_entrega_pendente_marca_como_convertida(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();
        $programacao = ProgramacaoViagem::factory()->confirmada()->create(['viagem_id' => $viagem->id]);
        $entrega = $programacao->paradas()->create(['tipo' => 'entrega', 'cidade' => 'Maceió', 'uf' => 'AL', 'valor_frete' => 800]);
        $cliente = Cliente::factory()->create();

        $response = $this->post(route('cargas.store', $viagem), [
            'cliente_id'             => $cliente->id,
            'valor_frete'            => 800,
            'destino'                => 'Maceió',
            'destino_uf'             => 'AL',
            'parada_programacao_id'  => $entrega->id,
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));
        $carga = Carga::firstOrFail();
        $this->assertSame('Maceió', $carga->destino);
        $this->assertSame($carga->id, $entrega->fresh()->carga_id);
        $this->assertCount(0, $viagem->fresh()->entregasPendentes());
    }

    public function test_carga_com_origem_propria_diferente_da_viagem(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create(['origem' => 'São Paulo', 'origem_uf' => 'SP']);
        $cliente = Cliente::factory()->create();

        $this->post(route('cargas.store', $viagem), [
            'cliente_id'              => $cliente->id,
            'origem'                  => 'Curitiba',
            'origem_uf'               => 'PR',
            'origem_codigo_municipio' => '4106902',
        ]);

        $carga = Carga::firstOrFail();
        $this->assertSame('Curitiba', $carga->origem_efetiva);
        $this->assertSame('4106902', $carga->origem_codigo_municipio_efetiva);
    }

    public function test_carga_sem_origem_propria_usa_a_origem_da_viagem(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create(['origem' => 'São Paulo', 'origem_uf' => 'SP']);
        $carga = Carga::factory()->create(['viagem_id' => $viagem->id]);

        $this->assertSame('São Paulo', $carga->origem_efetiva);
        $this->assertSame('SP', $carga->origem_uf_efetiva);
    }

    public function test_entrega_pendente_de_outra_viagem_nao_e_marcada_como_convertida(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();
        $outraViagem = Viagem::factory()->create();
        $outraProgramacao = ProgramacaoViagem::factory()->confirmada()->create(['viagem_id' => $outraViagem->id]);
        $entregaDeOutraViagem = $outraProgramacao->paradas()->create(['tipo' => 'entrega', 'cidade' => 'Maceió', 'uf' => 'AL']);
        $cliente = Cliente::factory()->create();

        $response = $this->post(route('cargas.store', $viagem), [
            'cliente_id'            => $cliente->id,
            'parada_programacao_id' => $entregaDeOutraViagem->id,
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertNull($entregaDeOutraViagem->fresh()->carga_id);
    }

    public function test_carga_herda_unidade_da_viagem_quando_nao_informada(): void
    {
        $this->actingAs(User::factory()->create());
        $unidade = Unidade::factory()->create();
        $viagem = Viagem::factory()->create(['unidade_id' => $unidade->id]);
        $cliente = Cliente::factory()->create();

        $this->post(route('cargas.store', $viagem), [
            'cliente_id' => $cliente->id,
        ]);

        $this->assertDatabaseHas('cargas', [
            'viagem_id' => $viagem->id,
            'unidade_id' => $unidade->id,
        ]);
    }

    public function test_nao_permite_criar_carga_em_viagem_de_outra_empresa(): void
    {
        // Usuário/cliente da empresa "padrão" precisam ser criados antes de
        // trocar o actingAs — o argumento de actingAs(User::factory()->create())
        // é avaliado com o auth ainda antigo, então criar depois herdaria o
        // tenant errado (mesmo bug que motivou o padrão de passar empresa_id
        // explícito nos outros testes multi-tenant deste arquivo/projeto).
        $usuarioPadrao = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $outraEmpresa = Empresa::factory()->create();
        $usuarioDeOutraEmpresa = User::factory()->create(['empresa_id' => $outraEmpresa->id]);
        $this->actingAs($usuarioDeOutraEmpresa);
        $viagemDeOutraEmpresa = Viagem::factory()->create();

        $this->actingAs($usuarioPadrao);

        $response = $this->post(route('cargas.store', $viagemDeOutraEmpresa), [
            'cliente_id' => $cliente->id,
        ]);

        $response->assertNotFound();
    }

    public function test_remove_carga_sem_documentos_ou_emissoes(): void
    {
        $this->actingAs(User::factory()->create());
        $carga = Carga::factory()->create();

        $response = $this->delete(route('cargas.destroy', $carga));

        $response->assertRedirect(route('viagens.show', $carga->viagem));
        $this->assertDatabaseMissing('cargas', ['id' => $carga->id]);
    }

    public function test_nao_remove_carga_com_documento_vinculado(): void
    {
        $this->actingAs(User::factory()->create());
        $carga = Carga::factory()->create();
        Documento::factory()->create(['viagem_id' => $carga->viagem_id, 'carga_id' => $carga->id]);

        $response = $this->delete(route('cargas.destroy', $carga));

        $response->assertStatus(422);
        $this->assertDatabaseHas('cargas', ['id' => $carga->id]);
    }

    public function test_nao_remove_carga_com_emissao_fiscal_vinculada(): void
    {
        $this->actingAs(User::factory()->create());
        $carga = Carga::factory()->create();
        EmissaoFiscal::factory()->paraCarga($carga)->create();

        $response = $this->delete(route('cargas.destroy', $carga));

        $response->assertStatus(422);
        $this->assertDatabaseHas('cargas', ['id' => $carga->id]);
    }
}
