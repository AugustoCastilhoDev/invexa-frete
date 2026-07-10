<?php

namespace Tests\Feature\Viagens;

use App\Models\Documento;
use App\Models\Empresa;
use App\Models\EmissaoFiscal;
use App\Models\User;
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
        $viagem = Viagem::factory()->create();

        $response = $this->post(route('viagens.emissoes-fiscais.store', [$viagem, 'cte']));

        $response->assertForbidden();
        $this->assertSame(0, EmissaoFiscal::count());
    }

    public function test_emissao_de_cte_cria_registro_e_aplica_resposta_processando(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();

        Http::fake([
            '*/v2/cte*' => Http::response(['status' => 'processando_autorizacao'], 202),
        ]);

        $response = $this->post(route('viagens.emissoes-fiscais.store', [$viagem, 'cte']));

        $response->assertRedirect(route('viagens.show', $viagem));
        $emissao = EmissaoFiscal::firstOrFail();
        $this->assertSame('cte', $emissao->tipo);
        $this->assertSame('processando_autorizacao', $emissao->status);
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

        $this->post(route('viagens.emissoes-fiscais.store', [$viagem, 'mdfe']));

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
        $viagem = Viagem::factory()->create();

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('timeout');
        });

        $response = $this->post(route('viagens.emissoes-fiscais.store', [$viagem, 'cte']));

        $response->assertRedirect(route('viagens.show', $viagem));
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
}
