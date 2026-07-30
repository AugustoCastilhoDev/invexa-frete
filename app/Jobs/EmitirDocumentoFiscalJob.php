<?php

namespace App\Jobs;

use App\Models\EmissaoFiscal;
use App\Services\FocusNfe\FocusNfeClient;
use App\Support\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Tira a chamada à Focus NFe de dentro do ciclo da requisição HTTP — cada
 * emissão faz uma única chamada de rede, e a Focus já é assíncrona do lado
 * dela (autorização real chega depois, por consulta manual ou webhook).
 * Enfileirar existe pra não deixar nem uma emissão avulsa nem um lote
 * fechado de muitas de uma vez (ex.: fechamento de carga no fim do dia)
 * esperando essa chamada dentro do tempo de execução do PHP-FPM.
 *
 * O worker roda sem sessão HTTP autenticada, então TenantContext::id() não
 * teria como resolver a empresa sozinho — handle()/failed() forçam o tenant
 * pela empresa da própria emissão antes de tocar em qualquer relação
 * (ex.: Documento::updateOrCreate dentro de aplicarRespostaFocus()).
 */
class EmitirDocumentoFiscalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Cobre reinício/crash do worker no meio do processamento (o job volta
     * pra fila sozinho após retry_after) — não é retry de erro de negócio:
     * uma resposta de erro da Focus é tratada dentro de handle() e nunca
     * lança exceção, então nunca consome essas tentativas.
     */
    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public EmissaoFiscal $emissao)
    {
    }

    public function handle(FocusNfeClient $focusNfe): void
    {
        TenantContext::runAs($this->emissao->empresa_id, function () use ($focusNfe) {
            $emissao = $this->emissao->fresh();

            // Já processada (reentrega da fila após já ter sido concluída) ou
            // apagada nesse meio tempo — nada a fazer.
            if (! $emissao || $emissao->status !== 'na_fila') {
                return;
            }

            $resposta = $emissao->tipo === 'cte'
                ? $focusNfe->emitirCte($emissao->empresa, $emissao->referencia, $emissao->payload_enviado)
                : $focusNfe->emitirMdfe($emissao->empresa, $emissao->referencia, $emissao->payload_enviado);

            if (! $resposta) {
                $emissao->update([
                    'status' => 'erro_autorizacao',
                    'mensagem_erro' => 'Não foi possível iniciar a emissão — veja os logs da aplicação.',
                ]);

                return;
            }

            $emissao->aplicarRespostaFocus($resposta);
        });
    }

    public function failed(\Throwable $e): void
    {
        TenantContext::runAs($this->emissao->empresa_id, function () use ($e) {
            $emissao = $this->emissao->fresh();

            if ($emissao && ! $emissao->isFinal()) {
                $emissao->update([
                    'status' => 'erro_autorizacao',
                    'mensagem_erro' => 'Falha inesperada ao processar a emissão — veja os logs da aplicação.',
                ]);
            }

            Log::error("EmitirDocumentoFiscalJob: falhou definitivamente para a emissão #{$this->emissao->id}.", [
                'erro' => $e->getMessage(),
            ]);
        });
    }
}
