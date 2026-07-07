<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\HasUploadedFile;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lancamento extends Model
{
    use BelongsToEmpresa, HasFactory, HasUploadedFile, TracksUser;

    protected $fillable = [
        'viagem_id',
        'tipo',
        'descricao',
        'valor',
        'km_veiculo',
        'litros',
        'data_lancamento',
        'comprovante',
        'observacao',
    ];

    protected $casts = [
        'data_lancamento' => 'date',
        'valor'           => 'decimal:2',
        'litros'          => 'decimal:2',
    ];

    // Lançamento pertence a uma viagem
    public function viagem()
    {
        return $this->belongsTo(Viagem::class);
    }

    // Accessor: URL para baixar o comprovante (assinada e temporária, se o disco for a nuvem)
    public function getComprovanteUrlAttribute(): ?string
    {
        return $this->uploadedFileUrl($this->comprovante);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'aprovado'  => 'success',
            'rejeitado' => 'danger',
            default     => 'warning',
        };
    }

    // Após salvar, recalcula os totais da viagem
    protected static function booted(): void
    {
        static::saved(function ($lancamento) {
            $lancamento->viagem->recalcularTotais();
        });

        static::deleted(function ($lancamento) {
            $lancamento->viagem->recalcularTotais();
        });
    }
}