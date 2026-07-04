<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\TracksDeletingUser;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends Model
{
    use BelongsToEmpresa, HasFactory, SoftDeletes, TracksUser, TracksDeletingUser;

    protected $fillable = [
        'placa',
        'modelo',
        'marca',
        'ano',
        'tipo',
        'renavam',
        'capacidade_kg',
        'status',
    ];

    // Um veículo tem muitas viagens
    public function viagens()
    {
        return $this->hasMany(Viagem::class);
    }

    // Um veículo tem muitas manutenções
    public function manutencoes()
    {
        return $this->hasMany(Manutencao::class)->orderByDesc('data_manutencao');
    }

    public function scopeEmManutencao($query)
    {
        return $query->where('status', 'manutencao');
    }
}