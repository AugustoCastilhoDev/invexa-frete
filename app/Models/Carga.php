<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\RegistraAuditoria;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carga extends Model
{
    use BelongsToEmpresa, HasFactory, RegistraAuditoria, TracksUser;

    protected $fillable = [
        'viagem_id',
        'cliente_id',
        'unidade_id',
        'valor_frete',
        'destino',
        'destino_uf',
        'destino_codigo_municipio',
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

    // Unidade (matriz/filial) que emite o CT-e desta carga — nullable, com
    // fallback pros dados fiscais da Empresa quando não preenchida.
    public function unidade()
    {
        return $this->belongsTo(Unidade::class);
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

    // Fracionado com destinos diferentes: quando a carga não tem cidade
    // própria (caso comum, uma entrega só na viagem), usa o destino da
    // Viagem — mesmo padrão de fallback já usado em unidade() pro CT-e.
    public function getDestinoEfetivoAttribute(): ?string
    {
        return $this->destino ?? $this->viagem?->destino;
    }

    public function getDestinoUfEfetivoAttribute(): ?string
    {
        return $this->destino_uf ?? $this->viagem?->destino_uf;
    }

    public function getDestinoCodigoMunicipioEfetivoAttribute(): ?string
    {
        return $this->destino_codigo_municipio ?? $this->viagem?->destino_codigo_municipio;
    }
}
