<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Motorista extends Model
{
    use HasFactory, SoftDeletes;

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
}