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
        'chassi',
        'validade_documento',
        'cavalo_id',
        'capacidade_kg',
        'status',
    ];

    protected $casts = [
        'validade_documento' => 'date',
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

    // Cavalo mecânico ao qual esta carreta está vinculada
    public function cavalo()
    {
        return $this->belongsTo(Veiculo::class, 'cavalo_id');
    }

    // Carreta(s) vinculada(s) a este cavalo mecânico
    public function carretas()
    {
        return $this->hasMany(Veiculo::class, 'cavalo_id');
    }

    public function scopeEmManutencao($query)
    {
        return $query->where('status', 'manutencao');
    }

    // Conjunto (cavalo + carreta) conta como 1 veículo no limite do plano:
    // a carreta só entra na contagem separadamente enquanto não está vinculada a um cavalo.
    public function scopeContamParaLimite($query)
    {
        return $query->where(function ($q) {
            $q->where('tipo', '!=', 'carreta')->orWhereNull('cavalo_id');
        });
    }
}