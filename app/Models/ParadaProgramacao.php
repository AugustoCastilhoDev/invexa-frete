<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\RegistraAuditoria;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Parada adicional de uma Programação — coleta OU entrega, além da
// origem/destino principal (ex.: 2 coletas + 2 entregas na mesma viagem).
// Unificado num conceito só (em vez de "destino adicional" separado de
// "origem adicional") pra já deixar a sequência real de paradas pronta
// pra roteirização futura, que precisa da ordem completa, misturando
// coleta e entrega.
class ParadaProgramacao extends Model
{
    use BelongsToEmpresa, HasFactory, RegistraAuditoria, TracksUser;

    protected $table = 'paradas_programacao';

    protected $fillable = [
        'programacao_viagem_id',
        'tipo',
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

    // Preenchida quando o operador confirma uma parada de entrega como
    // Carga de verdade na Viagem (ver CargasController::store()) — sem
    // isso, uma entrega já usada continuaria aparecendo pra sempre como
    // pendente. Paradas de coleta não usam este campo (não viram Carga
    // diretamente — ver Carga::origem, que é preenchida à mão).
    public function carga()
    {
        return $this->belongsTo(Carga::class);
    }

    public function ehColeta(): bool
    {
        return $this->tipo === 'coleta';
    }

    public function ehEntrega(): bool
    {
        return $this->tipo === 'entrega';
    }

    public function foiConvertidoEmCarga(): bool
    {
        return $this->carga_id !== null;
    }
}
