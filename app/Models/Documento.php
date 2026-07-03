<?php

namespace App\Models;

use App\Models\Concerns\HasUploadedFile;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory, HasUploadedFile, TracksUser;

    protected $fillable = [
        'viagem_id',
        'tipo',
        'numero',
        'chave_acesso',
        'serie',
        'data_emissao',
        'valor',
        'status',
        'arquivo',
        'observacao',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'valor'        => 'decimal:2',
    ];

    // Documento pertence a uma viagem
    public function viagem()
    {
        return $this->belongsTo(Viagem::class);
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    // Accessor: tipo formatado
    public function getTipoFormatadoAttribute(): string
    {
        return match($this->tipo) {
            'cte'    => 'CT-e',
            'mdfe'   => 'MDF-e',
            'nfe'    => 'NF-e',
            'outros' => 'Outros',
            default  => $this->tipo,
        };
    }

    // Accessor: badge de status
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'autorizado' => 'success',
            'cancelado'  => 'danger',
            default      => 'warning',
        };
    }

    // Accessor: URL para baixar o arquivo (assinada e temporária, se o disco for a nuvem)
    public function getArquivoUrlAttribute(): ?string
    {
        return $this->uploadedFileUrl($this->arquivo);
    }
}