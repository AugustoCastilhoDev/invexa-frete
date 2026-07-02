<?php

namespace App\Models;

use App\Models\Concerns\TracksDeletingUser;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Motorista extends Model
{
    use HasFactory, SoftDeletes, TracksUser, TracksDeletingUser;

    protected $fillable = [
        'nome',
        'cpf',
        'cnh',
        'categoria_cnh',
        'validade_cnh',
        'telefone',
        'email',
        'percentual_comissao',
        'status',
    ];

    protected $casts = [
        'validade_cnh' => 'date',
        'percentual_comissao' => 'decimal:2',
    ];

    // Um motorista tem muitas viagens
    public function viagens()
    {
        return $this->hasMany(Viagem::class);
    }

    // Motoristas ativos com CNH já vencida ou vencendo nos próximos $dias
    public function scopeCnhVencendo($query, int $dias = 30)
    {
        return $query->where('status', 'ativo')
            ->whereNotNull('validade_cnh')
            ->where('validade_cnh', '<=', now()->addDays($dias));
    }
}