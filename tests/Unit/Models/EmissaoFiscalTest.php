<?php

namespace Tests\Unit\Models;

use App\Models\Documento;
use App\Models\EmissaoFiscal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

    public function test_aplicar_resposta_autorizado_baixa_e_armazena_xml_e_pdf(): void
    {
        Storage::fake(config('filesystems.uploads_disk'));
        Http::fake([
            'https://focus.example/arquivo.xml' => Http::response('<xml>conteudo</xml>', 200),
            'https://focus.example/arquivo.pdf' => Http::response('%PDF-conteudo', 200),
        ]);

        $emissao = EmissaoFiscal::factory()->create(['tipo' => 'cte', 'referencia' => 'ref-download-1']);

        $emissao->aplicarRespostaFocus([
            'status' => 'autorizado',
            'chave_nfe' => str_repeat('1', 44),
            'numero' => '123456',
            'caminho_xml' => 'https://focus.example/arquivo.xml',
            'caminho_danfe' => 'https://focus.example/arquivo.pdf',
        ]);

        $emissao->refresh();
        $this->assertSame('documentos/ref-download-1.xml', $emissao->arquivo_xml);
        $this->assertSame('documentos/ref-download-1.pdf', $emissao->arquivo_pdf);
        Storage::disk(config('filesystems.uploads_disk'))->assertExists('documentos/ref-download-1.xml');
        Storage::disk(config('filesystems.uploads_disk'))->assertExists('documentos/ref-download-1.pdf');

        $documento = Documento::findOrFail($emissao->documento_id);
        $this->assertSame('documentos/ref-download-1.pdf', $documento->arquivo);
    }

    public function test_falha_ao_baixar_arquivo_nao_impede_atualizacao_de_status(): void
    {
        Storage::fake(config('filesystems.uploads_disk'));
        Http::fake([
            'https://focus.example/arquivo.pdf' => Http::response('erro', 500),
        ]);

        $emissao = EmissaoFiscal::factory()->create(['tipo' => 'cte', 'referencia' => 'ref-download-2']);

        $emissao->aplicarRespostaFocus([
            'status' => 'autorizado',
            'numero' => '123456',
            'caminho_danfe' => 'https://focus.example/arquivo.pdf',
        ]);

        $emissao->refresh();
        $this->assertSame('autorizado', $emissao->status);
        $this->assertNull($emissao->arquivo_pdf);
        $this->assertNotNull($emissao->documento_id);
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

    public function test_aplicar_encerramento_com_sucesso_marca_encerrado(): void
    {
        $emissao = EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'mdfe']);

        $emissao->aplicarEncerramento([
            'status' => 'encerrado',
            'status_sefaz' => '135',
            'mensagem_sefaz' => 'Evento registrado e vinculado a MDF-e',
        ]);

        $emissao->refresh();
        $this->assertSame('encerrado', $emissao->status);
        $this->assertSame('135', $emissao->protocolo_encerramento);
        $this->assertNotNull($emissao->encerrado_em);
        $this->assertNull($emissao->mensagem_erro);
    }

    public function test_aplicar_encerramento_com_erro_nao_preenche_encerrado_em(): void
    {
        $emissao = EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'mdfe']);

        $emissao->aplicarEncerramento([
            'status' => 'erro_encerramento',
            'mensagem_sefaz' => 'MDF-e não encontrado',
        ]);

        $emissao->refresh();
        $this->assertSame('erro_encerramento', $emissao->status);
        $this->assertNull($emissao->encerrado_em);
        $this->assertSame('MDF-e não encontrado', $emissao->mensagem_erro);
    }

    public function test_pode_encerrar(): void
    {
        $cte = EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'cte']);
        $mdfeProcessando = EmissaoFiscal::factory()->create(['tipo' => 'mdfe', 'status' => 'processando_autorizacao']);
        $mdfeAutorizado = EmissaoFiscal::factory()->autorizada()->create(['tipo' => 'mdfe']);
        $mdfeEncerrado = EmissaoFiscal::factory()->encerrada()->create();

        $this->assertFalse($cte->podeEncerrar());
        $this->assertFalse($mdfeProcessando->podeEncerrar());
        $this->assertTrue($mdfeAutorizado->podeEncerrar());
        $this->assertFalse($mdfeEncerrado->podeEncerrar());
    }
}
