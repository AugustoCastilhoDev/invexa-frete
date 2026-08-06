<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\RegistraAuditoria;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DestinoProgramacao extends Model
{
    use BelongsToEmpresa, HasFactory, RegistraAuditoria, TracksUser;

    protected $table = 'destinos_programacao';

    protected $fillable = [
        'programacao_viagem_id',
        'cidade',
        'uf',
        'codigo_municipio',
        'valor_frete',
        'ordem',
    ];

    protected $casts = [
        'valor_frete' => 'decimal:2',
    ];

    public function programacao()
    {
        return $this->belongsTo(ProgramacaoViagem::class, 'programacao_viagem_id');
    }

    // Preenchida quando o operador confirma essa sugestão como Carga de
    // verdade na Viagem (ver CargasController::store()) — sem isso, um
    // destino já usado continuaria aparecendo pra sempre como pendente.
    public function carga()
    {
        return $this->belongsTo(Carga::class);
    }

    public function foiConvertidoEmCarga(): bool
    {
        return $this->carga_id !== null;
    }
}
