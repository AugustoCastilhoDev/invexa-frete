<?php

namespace Tests\Feature\Viagens;

use App\Models\Carga;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Empresa;
use App\Models\EmissaoFiscal;
use App\Models\Motorista;
use App\Models\Unidade;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmissoesFiscaisTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ativa o Focus NFe na empresa do tenant atual do teste (a que o
     * TestCase::setUp forçou) — não em Empresa::first(), porque uma
     * migration cria uma "Empresa Padrão" fixa que também existe em todo
     * banco de teste e seria a primeira linha.
     */
    private function ativarFocusNfeNaEmpresaDeTeste(): Empresa
    {
        $empresa = Empresa::findOrFail(TenantContext::id());
        $empresa->update([
            'focus_nfe_ativo' => true,
            'focus_nfe_ambiente' => 'homologacao',
            'focus_nfe_token' => 'token-teste',
        ]);

        return $empresa->fresh();
    }

    public function test_nega_emissao_quando_empresa_nao_tem_focus_nfe_ativo(): void
    {
        $this->actingAs(User::factory()->create());
        $carga = Carga::factory()->create();

        $response = $this->post(route('cargas.emitir-cte', $carga));

        $response->assertForbidden();
        $this->assertSame(0, EmissaoFiscal::count());
    }

    public function test_emissao_de_cte_cria_registro_e_aplica_resposta_processando(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $this->actingAs(User::factory()->create());
        $carga = Carga::factory()->create();

        Http::fake([
            '*/v2/cte*' => Http::response(['status' => 'processando_autorizacao'], 202),
        ]);

        $response = $this->post(route('cargas.emitir-cte', $carga));

        $response->assertRedirect(route('viagens.show', $carga->viagem));
        $emissao = EmissaoFiscal::firstOrFail();
        $this->assertSame('cte', $emissao->tipo);
        $this->assertSame('processando_autorizacao', $emissao->status);
        $this->assertSame($carga->id, $emissao->carga_id);
        $this->assertSame($carga->viagem_id, $emissao->viagem_id);
        $this->assertNull($emissao->documento_id);
    }

    public function test_emissao_autorizada_de_imediato_cria_documento(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();

        Http::fake([
            '*/v2/mdfe*' => Http::response([
                'status' => 'autorizado',
                'chave_nfe' => '35250000000000000000550010000000011000000017',
                'numero' => '9999',
            ], 200),
        ]);

        $this->post(route('viagens.emitir-mdfe', $viagem));

        $emissao = EmissaoFiscal::firstOrFail();
        $this->assertSame('autorizado', $emissao->status);
        $this->assertNotNull($emissao->documento_id);
        $this->assertDatabaseHas('documentos', [
            'id' => $emissao->documento_id,
            'tipo' => 'mdfe',
            'status' => 'autorizado',
        ]);
    }

    public function test_falha_de_transporte_marca_emissao_com_erro(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $this->actingAs(User::factory()->create());
        $carga = Carga::factory()->create();

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('timeout');
        });

        $response = $this->post(route('cargas.emitir-cte', $carga));

        $response->assertRedirect(route('viagens.show', $carga->viagem));
        $emissao = EmissaoFiscal::firstOrFail();
        $this->assertSame('erro_autorizacao', $emissao->status);
        $this->assertSame(0, Documento::count());
    }

    public function test_atualizar_status_resincroniza_emissao_pendente(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();
        $emissao = EmissaoFiscal::factory()->create([
            'viagem_id' => $viagem->id,
            'tipo' => 'cte',
            'status' => 'processando_autorizacao',
        ]);

        Http::fake([
            '*/v2/cte/*' => Http::response(['status' => 'autorizado', 'chave_nfe' => str_repeat('1', 44)], 200),
        ]);

        $response = $this->post(route('emissoes-fiscais.atualizar-status', $emissao));

        $response->assertRedirect();
        $this->assertSame('autorizado', $emissao->fresh()->status);
    }

    public function test_emissao_de_uma_empresa_nao_e_acessivel_por_outra(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $emissao = EmissaoFiscal::factory()->create();

        $outraEmpresa = Empresa::factory()->focusNfeAtivo()->create();
        $outroUsuario = User::factory()->create(['empresa_id' => $outraEmpresa->id]);
        $this->actingAs($outroUsuario);

        $response = $this->post(route('emissoes-fiscais.atualizar-status', $emissao));

        $response->assertNotFound();
    }

    public function test_encerrar_mdfe_autorizado_com_sucesso(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $this->actingAs(User::factory()->create());
        $emissao = EmissaoFiscal::factory()->autorizada()->create([
            'tipo' => 'mdfe',
            'viagem_id' => Viagem::factory()->create()->id,
        ]);

        Http::fake([
            '*/v2/mdfe/*/encerrar' => Http::response([
                'status' => 'encerrado',
                'status_sefaz' => '135',
                'mensagem_sefaz' => 'Evento registrado e vinculado a MDF-e',
            ], 200),
        ]);

        $response = $this->post(route('emissoes-fiscais.encerrar', $emissao), [
            'data' => now()->format('Y-m-d'),
            'sigla_uf' => 'SP',
            'nome_municipio' => 'São Paulo',
        ]);

        $response->assertRedirect();
        $emissao->refresh();
        $this->assertSame('encerrado', $emissao->status);
        $this->assertNotNull($emissao->encerrado_em);
    }

    public function test_encerrar_mdfe_com_falha_da_focus_mantem_erro(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $this->actingAs(User::factory()->create());
        $emissao = EmissaoFiscal::factory()->autorizada()->create([
            'tipo' => 'mdfe',
            'viagem_id' => Viagem::factory()->create()->id,
        ]);

        Http::fake([
            '*/v2/mdfe/*/encerrar' => Http::response([
                'status' => 'erro_encerramento',
                'mensagem_sefaz' => 'MDF-e não encontrado',
            ], 422),
        ]);

        $response = $this->post(route('emissoes-fiscais.encerrar', $emissao), [
            'data' => now()->format('Y-m-d'),
            'sigla_uf' => 'SP',
            'nome_municipio' => 'São Paulo',
        ]);

        $response->assertRedirect();
        $emissao->refresh();
        $this->assertSame('erro_encerramento', $emissao->status);
        $this->assertNull($emissao->encerrado_em);
    }

    public function test_payload_do_cte_usa_campos_planos_da_focus(): void
    {
        $empresa = $this->ativarFocusNfeNaEmpresaDeTeste();
        $empresa->update([
            'codigo_municipio' => '4106902',
            'municipio' => 'Curitiba',
            'uf' => 'PR',
            'logradouro' => 'Rua das Flores',
            'numero' => '100',
            'bairro' => 'Centro',
            'cep' => '80010-000',
            'telefone' => '(41) 3333-4444',
            'inscricao_estadual' => '1234567890',
            'rntrc' => '12345678',
            'cfop_padrao' => '6353',
            'icms_situacao_tributaria' => '40',
            'icms_aliquota' => 12.00,
        ]);
        $this->actingAs(User::factory()->create());

        $cliente = Cliente::factory()->create([
            'nome' => 'Cliente Teste',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
        ]);
        $viagem = Viagem::factory()->create([
            'origem' => 'Curitiba',
            'origem_uf' => 'PR',
            'origem_codigo_municipio' => '4106902',
            'destino' => 'São Paulo',
            'destino_uf' => 'SP',
            'destino_codigo_municipio' => '3550308',
            'descricao_carga' => 'Eletrônicos',
        ]);
        $carga = Carga::factory()->create([
            'viagem_id' => $viagem->id,
            'cliente_id' => $cliente->id,
            'valor_frete' => 800.00,
        ]);
        Documento::factory()->create([
            'viagem_id' => $viagem->id,
            'carga_id' => $carga->id,
            'tipo' => 'nfe',
            'valor' => 1500.00,
        ]);

        Http::fake(['*/v2/cte*' => Http::response(['status' => 'processando_autorizacao'], 202)]);

        $this->post(route('cargas.emitir-cte', $carga));

        Http::assertSent(function ($request) use ($empresa, $cliente) {
            return str_contains($request->url(), '/v2/cte')
                && $request['cnpj_emitente'] === $empresa->cnpj
                && $request['uf_emitente'] === 'PR'
                && $request['codigo_municipio_envio'] === '4106902'
                && $request['cfop'] === '6353'
                && $request['icms_aliquota'] == 12.00
                && $request['nome_destinatario'] === 'Cliente Teste'
                && $request['municipio_destinatario'] === 'São Paulo'
                && $request['uf_destinatario'] === 'SP'
                && $request['municipio_inicio'] === 'Curitiba'
                && $request['codigo_municipio_fim'] === '3550308'
                && $request['produto_predominante'] === 'Eletrônicos'
                && $request['valor_total'] == 800.00
                && $request['valor_receber'] == 800.00
                && $request['valor_total_carga'] == 1500.00
                && $request['modal_rodoviario']['rntrc'] === '12345678';
        });
    }

    public function test_payload_do_cte_usa_cnpj_da_unidade_quando_a_carga_tem_uma(): void
    {
        $empresa = $this->ativarFocusNfeNaEmpresaDeTeste();
        $empresa->update(['cnpj' => '11.111.111/0001-11', 'municipio' => 'Curitiba', 'uf' => 'PR']);
        $this->actingAs(User::factory()->create());

        $unidade = Unidade::factory()->create([
            'empresa_id' => $empresa->id,
            'nome' => 'Filial — Minas Gerais',
            'cnpj' => '11.111.111/0002-92',
            'municipio' => 'Belo Horizonte',
            'uf' => 'MG',
            'rntrc' => '99999999',
        ]);
        $cliente = Cliente::factory()->create();
        $viagem = Viagem::factory()->create(['unidade_id' => $unidade->id]);
        $carga = Carga::factory()->create([
            'viagem_id' => $viagem->id,
            'cliente_id' => $cliente->id,
            'unidade_id' => $unidade->id,
        ]);

        Http::fake(['*/v2/cte*' => Http::response(['status' => 'processando_autorizacao'], 202)]);

        $this->post(route('cargas.emitir-cte', $carga));

        Http::assertSent(function ($request) use ($unidade) {
            return str_contains($request->url(), '/v2/cte')
                && $request['cnpj_emitente'] === $unidade->cnpj
                && $request['municipio_envio'] === 'Belo Horizonte'
                && $request['uf_envio'] === 'MG'
                && $request['modal_rodoviario']['rntrc'] === '99999999';
        });
    }

    public function test_payload_do_cte_usa_empresa_quando_carga_nao_tem_unidade(): void
    {
        $empresa = $this->ativarFocusNfeNaEmpresaDeTeste();
        $empresa->update(['cnpj' => '11.111.111/0001-11', 'municipio' => 'Curitiba', 'uf' => 'PR']);
        $this->actingAs(User::factory()->create());
        $carga = Carga::factory()->create();

        Http::fake(['*/v2/cte*' => Http::response(['status' => 'processando_autorizacao'], 202)]);

        $this->post(route('cargas.emitir-cte', $carga));

        Http::assertSent(function ($request) use ($empresa) {
            return str_contains($request->url(), '/v2/cte')
                && $request['cnpj_emitente'] === $empresa->cnpj
                && $request['municipio_envio'] === 'Curitiba';
        });
    }

    public function test_payload_do_mdfe_inclui_condutor_e_ctes_de_todas_as_cargas(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $motorista = Motorista::factory()->create(['nome' => 'João Silva', 'cpf' => '123.456.789-00']);
        $veiculo = Veiculo::factory()->create(['renavam' => '12345678900', 'capacidade_kg' => 15000, 'tara_kg' => 8000]);
        $viagem = Viagem::factory()->create([
            'motorista_id' => $motorista->id,
            'veiculo_id' => $veiculo->id,
            'origem' => 'Curitiba',
            'origem_uf' => 'PR',
            'origem_codigo_municipio' => '4106902',
            'destino' => 'São Paulo',
            'destino_uf' => 'SP',
        ]);
        $cargaA = Carga::factory()->create(['viagem_id' => $viagem->id]);
        $cargaB = Carga::factory()->create(['viagem_id' => $viagem->id]);
        $cteA = EmissaoFiscal::factory()->autorizada()->paraCarga($cargaA)->create([
            'chave_acesso' => str_repeat('1', 44),
        ]);
        $cteB = EmissaoFiscal::factory()->autorizada()->paraCarga($cargaB)->create([
            'chave_acesso' => str_repeat('2', 44),
        ]);
        $this->actingAs(User::factory()->create());

        Http::fake(['*/v2/mdfe*' => Http::response(['status' => 'processando_autorizacao'], 202)]);

        $this->post(route('viagens.emitir-mdfe', $viagem));

        Http::assertSent(function ($request) use ($veiculo, $cteA, $cteB) {
            $chaves = array_column($request['conhecimentos_transporte'], 'chave_cte');

            return str_contains($request->url(), '/v2/mdfe')
                && $request['placa_veiculo'] === $veiculo->placa
                && $request['renavam_veiculo'] === '12345678900'
                && $request['tara_veiculo'] === 8000
                && $request['condutores'][0]['nome'] === 'João Silva'
                && $request['condutores'][0]['cpf'] === '12345678900'
                && $request['municipios_carregamento'][0]['codigo'] === '4106902'
                && count($request['percursos']) === 2
                && count($chaves) === 2
                && in_array($cteA->chave_acesso, $chaves, true)
                && in_array($cteB->chave_acesso, $chaves, true);
        });
    }

    public function test_payload_do_mdfe_sem_cte_autorizado_manda_lista_vazia(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create(['origem_uf' => 'PR', 'destino_uf' => 'PR']);

        Http::fake(['*/v2/mdfe*' => Http::response(['status' => 'processando_autorizacao'], 202)]);

        $this->post(route('viagens.emitir-mdfe', $viagem));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v2/mdfe')
                && $request['conhecimentos_transporte'] === []
                && count($request['percursos']) === 1;
        });
    }

    public function test_nao_permite_encerrar_cte_ou_mdfe_nao_autorizado(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $this->actingAs(User::factory()->create());

        $cteAutorizado = EmissaoFiscal::factory()->autorizada()->create([
            'tipo' => 'cte',
            'viagem_id' => Viagem::factory()->create()->id,
        ]);
        $mdfeProcessando = EmissaoFiscal::factory()->create([
            'tipo' => 'mdfe',
            'status' => 'processando_autorizacao',
            'viagem_id' => Viagem::factory()->create()->id,
        ]);

        $payload = ['data' => now()->format('Y-m-d'), 'sigla_uf' => 'SP', 'nome_municipio' => 'São Paulo'];

        $this->post(route('emissoes-fiscais.encerrar', $cteAutorizado), $payload)->assertStatus(422);
        $this->post(route('emissoes-fiscais.encerrar', $mdfeProcessando), $payload)->assertStatus(422);
    }
}
