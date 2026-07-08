<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\TracksDeletingUser;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramacaoViagem extends Model
{
    use BelongsToEmpresa, HasFactory, SoftDeletes, TracksUser, TracksDeletingUser;

    protected $table = 'programacoes_viagem';

    protected $fillable = [
        'motorista_id',
        'veiculo_id',
        'cliente_id',
        'viagem_origem_id',
        'origem',
        'destino',
        'data_prevista',
        'observacoes',
    ];

    protected $casts = [
        'data_prevista' => 'date',
    ];

    public function motorista()
    {
        return $this->belongsTo(Motorista::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Viagem a partir da qual esta programação foi criada (rastreabilidade)
    public function viagemOrigem()
    {
        return $this->belongsTo(Viagem::class, 'viagem_origem_id');
    }

    // Viagem real, criada ao confirmar a programação
    public function viagem()
    {
        return $this->belongsTo(Viagem::class, 'viagem_id');
    }

    public function estaPendente(): bool
    {
        return $this->status === 'pendente';
    }
}
