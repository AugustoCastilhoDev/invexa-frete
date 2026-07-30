<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TabelaFrete extends Model
{
    use BelongsToEmpresa, HasFactory, RegistraAuditoria;

    protected $table = 'tabelas_frete';

    protected $fillable = [
        'cliente_id',
        'origem',
        'origem_uf',
        'origem_codigo_municipio',
        'destino',
        'destino_uf',
        'destino_codigo_municipio',
        'valor_frete',
    ];

    protected $casts = [
        'valor_frete' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
