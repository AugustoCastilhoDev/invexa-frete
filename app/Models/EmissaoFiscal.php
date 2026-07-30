<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\TracksDeletingUser;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmissaoFiscal extends Model
{
    use BelongsToEmpresa, HasFactory, SoftDeletes, TracksUser, TracksDeletingUser;

    protected $table = 'emissoes_fiscais';

    protected $fillable = [
        'viagem_id',
        'carga_id',
        'documento_id',
        'tipo',
        'referencia',
        'status',
        'chave_acesso',
        'numero',
        'serie',
        'protocolo_autorizacao',
        'codigo_erro',
        'mensagem_erro',
        'payload_enviado',
        'payload_resposta',
        'arquivo_xml',
        'arquivo_pdf',
        'autorizado_em',
        'encerrado_em',
        'protocolo_encerramento',
        'payload_encerramento',
        'justificativa_cancelamento',
        'cancelado_em',
        'protocolo_cancelamento',
        'payload_cancelamento',
    ];

    protected $casts = [
        'payload_enviado'       => 'array',
        'payload_resposta'      => 'array',
        'payload_encerramento'  => 'array',
        'payload_cancelamento'  => 'array',
        'autorizado_em'         => 'datetime',
        'encerrado_em'          => 'datetime',
        'cancelado_em'          => 'datetime',
    ];

    private const STATUS_FINAIS = ['autorizado', 'cancelado', 'erro_autorizacao', 'denegado', 'encerrado'];

    public function viagem()
    {
        return $this->belongsTo(Viagem::class);
    }

    // Só preenchido para tipo=cte (um CT-e pertence a uma carga/cliente); o
    // MDF-e cobre a viagem inteira e não tem carga própria.
    public function carga()
    {
        return $this->belongsTo(Carga::class);
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    // Histórico de cartas de correção — só existe pra tipo=cte
    public function cartasCorrecao()
    {
        return $this->hasMany(CartaCorrecaoCte::class)->latest();
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::STATUS_FINAIS, true);
    }

    public function podeEncerrar(): bool
    {
        return $this->tipo === 'mdfe' && $this->status === 'autorizado' && is_null($this->encerrado_em);
    }

    // Vale para cte e mdfe — a Focus só aceita cancelar documento com status
    // "autorizado" (confirmado em doc.focusnfe.com.br/reference/cancelar_cte_cte_os
    // e /cancelar_mdfe); um MDF-e já encerrado não fica mais "autorizado", então
    // essa checagem sozinha já cobre esse caso sem precisar olhar encerrado_em.
    public function podeCancelar(): bool
    {
        return $this->status === 'autorizado';
    }

    // Carta de correção (confirmado em doc.focusnfe.com.br/reference/carta_correcao_cte_cte_os)
    // só existe pra CT-e autorizado — a Focus rejeita com "CT-e não autorizado" fora disso.
    public function podeEmitirCartaCorrecao(): bool
    {
        return $this->tipo === 'cte' && $this->status === 'autorizado';
    }

    public function scopeMdfeAbertoDoVeiculo($query, int $veiculoId)
    {
        return $query->where('tipo', 'mdfe')
            ->where('status', 'autorizado')
            ->whereNull('encerrado_em')
            ->whereHas('viagem', fn ($q) => $q->where('veiculo_id', $veiculoId));
    }

    // Accessor: tipo formatado (mesmo padrão de Documento::getTipoFormatadoAttribute)
    public function getTipoFormatadoAttribute(): string
    {
        return match ($this->tipo) {
            'cte'  => 'CT-e',
            'mdfe' => 'MDF-e',
            default => $this->tipo,
        };
    }

    /**
     * Ponto único de aplicação de uma resposta da Focus NFe — usado tanto pela
     * chamada síncrona inicial (POST) quanto por uma consulta manual ou pelo
     * webhook, para não duplicar a lógica de sincronizar o Documento em três
     * lugares diferentes. Os nomes exatos das chaves no payload da Focus
     * precisam ser confirmados contra uma resposta real (sandbox/produção)
     * antes de considerar este mapeamento definitivo.
     */
    public function aplicarRespostaFocus(array $payload): void
    {
        $novoStatus = $payload['status'] ?? $this->status;

        $arquivoXml = $this->arquivo_xml;
        $arquivoPdf = $this->arquivo_pdf;

        // Só baixa quando de fato autorizado — antes disso a Focus normalmente
        // não tem XML/DACTE gerado ainda.
        if ($novoStatus === 'autorizado') {
            if ($urlXml = $payload['caminho_xml'] ?? null) {
                $arquivoXml = $this->baixarEArmazenar($urlXml, 'xml') ?? $arquivoXml;
            }

            if ($urlPdf = $payload['caminho_danfe'] ?? $payload['caminho_damdfe'] ?? null) {
                $arquivoPdf = $this->baixarEArmazenar($urlPdf, 'pdf') ?? $arquivoPdf;
            }
        }

        $this->update([
            'status'         => $novoStatus,
            'chave_acesso'   => $payload['chave_nfe'] ?? $payload['chave'] ?? $this->chave_acesso,
            'numero'         => $payload['numero'] ?? $this->numero,
            'serie'          => $payload['serie'] ?? $this->serie,
            'protocolo_autorizacao' => $payload['protocolo'] ?? $this->protocolo_autorizacao,
            'codigo_erro'    => $payload['codigo'] ?? null,
            'mensagem_erro'  => $payload['mensagem'] ?? null,
            'arquivo_xml'    => $arquivoXml,
            'arquivo_pdf'    => $arquivoPdf,
            'payload_resposta' => $payload,
            'autorizado_em'  => $novoStatus === 'autorizado' ? now() : $this->autorizado_em,
        ]);

        if ($this->status === 'autorizado') {
            $this->sincronizarDocumento();
        }
    }

    /**
     * Aplica a resposta do endpoint de encerramento de MDF-e — shape diferente
     * do usado por aplicarRespostaFocus() (status/status_sefaz/mensagem_sefaz/
     * caminho_xml, sem chave/numero/serie/protocolo), por isso não reaproveita
     * aquele método.
     */
    public function aplicarEncerramento(array $payload): void
    {
        $novoStatus = $payload['status'] ?? 'erro_encerramento';

        $this->update([
            'status' => $novoStatus,
            'protocolo_encerramento' => $payload['protocolo'] ?? $payload['status_sefaz'] ?? $this->protocolo_encerramento,
            'mensagem_erro' => $novoStatus === 'encerrado' ? null : ($payload['mensagem_sefaz'] ?? $payload['mensagem'] ?? null),
            'payload_encerramento' => $payload,
            'encerrado_em' => $novoStatus === 'encerrado' ? now() : $this->encerrado_em,
        ]);
    }

    /**
     * Aplica a resposta do endpoint de cancelamento (DELETE /v2/cte|mdfe/{ref})
     * — confirmado contra doc.focusnfe.com.br/reference/cancelar_cte_cte_os e
     * /cancelar_mdfe: mesmo shape de aplicarEncerramento() (status/status_sefaz/
     * mensagem_sefaz/caminho_xml), não o de aplicarRespostaFocus(). O Documento
     * vinculado (só existe depois de autorizado) acompanha o cancelamento pra
     * não ficar mostrando "autorizado" pra um CT-e/MDF-e que a SEFAZ já invalidou.
     */
    public function aplicarCancelamento(array $payload): void
    {
        $novoStatus = $payload['status'] ?? 'erro_cancelamento';

        $this->update([
            'status' => $novoStatus,
            'protocolo_cancelamento' => $payload['protocolo'] ?? $payload['status_sefaz'] ?? $this->protocolo_cancelamento,
            'mensagem_erro' => $novoStatus === 'cancelado' ? null : ($payload['mensagem_sefaz'] ?? $payload['mensagem'] ?? null),
            'payload_cancelamento' => $payload,
            'cancelado_em' => $novoStatus === 'cancelado' ? now() : $this->cancelado_em,
        ]);

        if ($novoStatus === 'cancelado' && $this->documento_id) {
            Documento::whereKey($this->documento_id)->update(['status' => 'cancelado']);
        }
    }

    /**
     * Registra uma carta de correção já aceita pela Focus/SEFAZ — diferente de
     * aplicarCancelamento()/aplicarEncerramento(), não muda o status da própria
     * EmissaoFiscal (o CT-e continua "autorizado"); só acumula histórico, já
     * que a Focus aceita até 20 cartas de correção por CT-e (só a última vale).
     */
    public function registrarCartaCorrecao(array $dadosCorrecao, array $respostaFocus): CartaCorrecaoCte
    {
        return $this->cartasCorrecao()->create([
            'grupo_corrigido' => $dadosCorrecao['grupo_corrigido'] ?? null,
            'campo_corrigido' => $dadosCorrecao['campo_corrigido'],
            'valor_corrigido' => $dadosCorrecao['valor_corrigido'],
            'numero_item_grupo_corrigido' => $dadosCorrecao['numero_item_grupo_corrigido'] ?? null,
            'numero_carta_correcao' => $respostaFocus['numero_carta_correcao'] ?? null,
            'status_sefaz' => $respostaFocus['status_sefaz'] ?? null,
            'mensagem_sefaz' => $respostaFocus['mensagem_sefaz'] ?? null,
            'caminho_xml' => $respostaFocus['caminho_xml'] ?? null,
        ]);
    }

    /**
     * A Focus NFe devolve o XML/DACTE como arquivo hospedado nos servidores
     * dela, não no nosso storage — baixamos e guardamos no disco configurado
     * (mesmo padrão dos uploads manuais em DocumentosController) para que o
     * link de download na tela seja permanente, e não dependa de uma URL
     * externa/temporária. Falha aqui nunca deve derrubar a atualização de
     * status vinda do webhook — só loga e segue sem arquivo.
     */
    private function baixarEArmazenar(string $url, string $extensao): ?string
    {
        try {
            $response = Http::timeout(10)->get($url);
        } catch (\Throwable $e) {
            Log::warning("EmissaoFiscal #{$this->id}: falha ao baixar arquivo da Focus NFe.", ['url' => $url, 'erro' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning("EmissaoFiscal #{$this->id}: resposta de erro ao baixar arquivo da Focus NFe.", ['url' => $url, 'status' => $response->status()]);

            return null;
        }

        $path = "documentos/{$this->referencia}.{$extensao}";

        Storage::disk(config('filesystems.uploads_disk'))->put($path, $response->body());

        return $path;
    }

    private function sincronizarDocumento(): void
    {
        $documento = Documento::updateOrCreate(
            ['id' => $this->documento_id],
            [
                'viagem_id'    => $this->viagem_id,
                'carga_id'     => $this->carga_id,
                'tipo'         => $this->tipo,
                'numero'       => $this->numero ?? '',
                'chave_acesso' => $this->chave_acesso,
                'serie'        => $this->serie,
                'data_emissao' => now()->toDateString(),
                'status'       => 'autorizado',
                'arquivo'      => $this->arquivo_pdf,
                'observacao'   => "Emitido automaticamente via Focus NFe (ref {$this->referencia})",
            ]
        );

        if ($documento->id !== $this->documento_id) {
            $this->update(['documento_id' => $documento->id]);
        }
    }
}
