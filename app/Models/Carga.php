<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carga extends Model
{
    use BelongsToEmpresa, HasFactory, TracksUser;

    protected $fillable = [
        'viagem_id',
        'cliente_id',
        'valor_frete',
    ];

    protected $casts = [
        'valor_frete' => 'decimal:2',
    ];

    public function viagem()
    {
        return $this->belongsTo(Viagem::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    public function emissoesFiscais()
    {
        return $this->hasMany(EmissaoFiscal::class);
    }

    // "Número de carga" exibido pro usuário — reaproveita o próprio id em vez
    // de manter uma coluna sequencial separada.
    public function getNumeroFormatadoAttribute(): string
    {
        return '#' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
