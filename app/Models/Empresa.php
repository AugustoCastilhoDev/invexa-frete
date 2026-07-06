<?php

namespace App\Models;

use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory, TracksUser;

    protected $fillable = [
        'nome',
        'cnpj',
        'status',
        'limite_veiculos',
        'plano',
        'ciclo_cobranca',
        'asaas_customer_id',
        'asaas_subscription_id',
        'asaas_status',
        'asaas_last_event_at',
    ];

    protected $casts = [
        'asaas_last_event_at' => 'datetime',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    // null = sem limite (ilimitado)
    public function limiteVeiculosAtingido(): bool
    {
        if ($this->limite_veiculos === null) {
            return false;
        }

        return $this->veiculos()->withoutGlobalScope('empresa')->contamParaLimite()->count() >= $this->limite_veiculos;
    }
}
