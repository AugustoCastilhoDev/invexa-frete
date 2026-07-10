<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\TracksDeletingUser;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmissaoFiscal extends Model
{
    use BelongsToEmpresa, HasFactory, SoftDeletes, TracksUser, TracksDeletingUser;

    protected $table = 'emissoes_fiscais';

    protected $fillable = [
        'viagem_id',
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
    ];

    protected $casts = [
        'payload_enviado'  => 'array',
        'payload_resposta' => 'array',
        'autorizado_em'    => 'datetime',
    ];

    private const STATUS_FINAIS = ['autorizado', 'cancelado', 'erro_autorizacao', 'denegado'];

    public function viagem()
    {
        return $this->belongsTo(Viagem::class);
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::STATUS_FINAIS, true);
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
        $this->update([
            'status'         => $payload['status'] ?? $this->status,
            'chave_acesso'   => $payload['chave_nfe'] ?? $payload['chave'] ?? $this->chave_acesso,
            'numero'         => $payload['numero'] ?? $this->numero,
            'serie'          => $payload['serie'] ?? $this->serie,
            'protocolo_autorizacao' => $payload['protocolo'] ?? $this->protocolo_autorizacao,
            'codigo_erro'    => $payload['codigo'] ?? null,
            'mensagem_erro'  => $payload['mensagem'] ?? null,
            'arquivo_xml'    => $payload['caminho_xml'] ?? $this->arquivo_xml,
            'arquivo_pdf'    => $payload['caminho_danfe'] ?? $payload['caminho_damdfe'] ?? $this->arquivo_pdf,
            'payload_resposta' => $payload,
            'autorizado_em'  => ($payload['status'] ?? null) === 'autorizado' ? now() : $this->autorizado_em,
        ]);

        if ($this->status === 'autorizado') {
            $this->sincronizarDocumento();
        }
    }

    private function sincronizarDocumento(): void
    {
        $documento = Documento::updateOrCreate(
            ['id' => $this->documento_id],
            [
                'viagem_id'    => $this->viagem_id,
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
