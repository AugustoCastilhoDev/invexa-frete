<?php

namespace Tests\Feature\EmissoesFiscais;

use App\Jobs\EmitirDocumentoFiscalJob;
use App\Models\Carga;
use App\Models\Documento;
use App\Models\Empresa;
use App\Models\EmissaoFiscal;
use App\Models\User;
use App\Models\Viagem;
use App\Services\FocusNfe\FocusNfeClient;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmitirDocumentoFiscalJobTest extends TestCase
{
    use RefreshDatabase;

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

    /**
     * Simula o worker real de fila: sem sessão HTTP autenticada e sem tenant
     * forçado — é exatamente o estado em que TenantContext::id() rodaria em
     * produção dentro de `php artisan queue:work`.
     */
    private function simularAmbienteDeWorker(): void
    {
        Auth::logout();
        TenantContext::forceId(null);
    }

    public function test_emissao_fica_na_fila_e_nao_chama_a_focus_na_propria_requisicao(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();

        Queue::fake();

        $response = $this->post(route('viagens.emitir-mdfe', $viagem));

        $response->assertRedirect(route('viagens.show', $viagem));
        $emissao = EmissaoFiscal::firstOrFail();
        $this->assertSame('na_fila', $emissao->status);

        Queue::assertPushed(
            EmitirDocumentoFiscalJob::class,
            fn ($job) => $job->emissao->is($emissao)
        );
        Http::assertNothingSent();
    }

    public function test_job_processado_pelo_worker_cria_documento_com_empresa_correta(): void
    {
        $empresa = $this->ativarFocusNfeNaEmpresaDeTeste();
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();

        Http::fake([
            '*/v2/mdfe*' => Http::response([
                'status' => 'autorizado',
                'chave_nfe' => str_repeat('1', 44),
                'numero' => '999',
            ], 200),
        ]);

        Queue::fake();
        $this->post(route('viagens.emitir-mdfe', $viagem));
        $emissao = EmissaoFiscal::firstOrFail();

        $this->simularAmbienteDeWorker();

        (new EmitirDocumentoFiscalJob($emissao))->handle(app(FocusNfeClient::class));

        $emissao->refresh();
        $this->assertSame('autorizado', $emissao->status);
        $this->assertNotNull($emissao->documento_id);
        $this->assertDatabaseHas('documentos', [
            'id' => $emissao->documento_id,
            'empresa_id' => $empresa->id,
        ]);
    }

    public function test_job_marca_erro_quando_focus_falha_mesmo_sem_sessao_http(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $carga = Carga::factory()->create();
        $emissao = EmissaoFiscal::create([
            'viagem_id' => $carga->viagem_id,
            'carga_id' => $carga->id,
            'tipo' => 'cte',
            'referencia' => 'invexa-teste-job-erro',
            'status' => 'na_fila',
            'payload_enviado' => [],
        ]);

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('timeout');
        });

        $this->simularAmbienteDeWorker();

        (new EmitirDocumentoFiscalJob($emissao))->handle(app(FocusNfeClient::class));

        $this->assertSame('erro_autorizacao', $emissao->fresh()->status);
        $this->assertSame(0, Documento::count());
    }

    public function test_job_ja_processado_nao_reenvia_para_a_focus_em_reentrega(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $carga = Carga::factory()->create();
        $emissao = EmissaoFiscal::create([
            'viagem_id' => $carga->viagem_id,
            'carga_id' => $carga->id,
            'tipo' => 'cte',
            'referencia' => 'invexa-teste-job-idempotente',
            'status' => 'autorizado',
        ]);

        $this->simularAmbienteDeWorker();

        (new EmitirDocumentoFiscalJob($emissao))->handle(app(FocusNfeClient::class));

        Http::assertNothingSent();
    }

    public function test_failed_marca_emissao_como_erro_mesmo_sem_sessao_http(): void
    {
        $this->ativarFocusNfeNaEmpresaDeTeste();
        $carga = Carga::factory()->create();
        $emissao = EmissaoFiscal::create([
            'viagem_id' => $carga->viagem_id,
            'carga_id' => $carga->id,
            'tipo' => 'cte',
            'referencia' => 'invexa-teste-job-failed',
            'status' => 'na_fila',
        ]);

        $this->simularAmbienteDeWorker();

        (new EmitirDocumentoFiscalJob($emissao))->failed(new \RuntimeException('falha simulada'));

        $this->assertSame('erro_autorizacao', $emissao->fresh()->status);
    }
}
