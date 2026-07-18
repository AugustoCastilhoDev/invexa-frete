<?php

namespace App\Models;

use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    use HasFactory, TracksUser;

    protected $fillable = [
        'nome',
        'cnpj',
        'inscricao_estadual',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'municipio',
        'codigo_municipio',
        'uf',
        'telefone',
        'rntrc',
        'cfop_padrao',
        'icms_situacao_tributaria',
        'icms_aliquota',
    ];

    protected $casts = [
        'icms_aliquota' => 'decimal:2',
        'cnpj' => 'encrypted',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function viagens()
    {
        return $this->hasMany(Viagem::class);
    }

    public function cargas()
    {
        return $this->hasMany(Carga::class);
    }
}
