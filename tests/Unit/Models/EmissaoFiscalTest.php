<?php

namespace Tests\Unit\Models;

use App\Models\Documento;
use App\Models\EmissaoFiscal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmissaoFiscalTest extends TestCase
{
    use RefreshDatabase;

    public function test_aplicar_resposta_processando_nao_cria_documento(): void
    {
        $emissao = EmissaoFiscal::factory()->create(['status' => 'processando_autorizacao']);

        $emissao->aplicarRespostaFocus(['status' => 'processando_autorizacao']);

        $this->assertNull($emissao->fresh()->documento_id);
        $this->assertSame(0, Documento::count());
    }

    public function test_aplicar_resposta_autorizado_cria_documento_e_vincula(): void
    {
        $emissao = EmissaoFiscal::factory()->create(['tipo' => 'cte', 'numero' => '123456']);

        $emissao->aplicarRespostaFocus([
            'status' => 'autorizado',
            'chave_nfe' => '35250000000000000000550010000000011000000017',
            'numero' => '123456',
        ]);

        $emissao->refresh();
        $this->assertSame('autorizado', $emissao->status);
        $this->assertNotNull($emissao->documento_id);

        $documento = Documento::findOrFail($emissao->documento_id);
        $this->assertSame('cte', $documento->tipo);
        $this->assertSame('autorizado', $documento->status);
        $this->assertSame('35250000000000000000550010000000011000000017', $documento->chave_acesso);
        $this->assertSame($emissao->viagem_id, $documento->viagem_id);
    }

    public function test_aplicar_resposta_de_erro_nao_cria_documento(): void
    {
        $emissao = EmissaoFiscal::factory()->create();

        $emissao->aplicarRespostaFocus([
            'status' => 'erro_autorizacao',
            'codigo' => 'campo_obrigatorio',
            'mensagem' => 'CFOP inválido',
        ]);

        $emissao->refresh();
        $this->assertSame('erro_autorizacao', $emissao->status);
        $this->assertSame('CFOP inválido', $emissao->mensagem_erro);
        $this->assertNull($emissao->documento_id);
        $this->assertSame(0, Documento::count());
    }

    public function test_tipo_formatado(): void
    {
        $cte = EmissaoFiscal::factory()->create(['tipo' => 'cte']);
        $mdfe = EmissaoFiscal::factory()->create(['tipo' => 'mdfe']);

        $this->assertSame('CT-e', $cte->tipo_formatado);
        $this->assertSame('MDF-e', $mdfe->tipo_formatado);
    }
}
