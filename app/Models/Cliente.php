<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tipo_pessoa',
        'nome',
        'razao_social',
        'cpf_cnpj',
        'ie',
        'email',
        'telefone',
        'celular',
        'contato',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'tabela_frete',
        'observacoes',
        'status',
    ];

    protected $casts = [
        'tabela_frete' => 'decimal:2',
    ];

    // Cliente tem muitas viagens
    public function viagens()
    {
        return $this->hasMany(Viagem::class);
    }

    // Accessor: documento formatado
    public function getDocumentoFormatadoAttribute(): string
    {
        $doc = preg_replace('/\D/', '', $this->cpf_cnpj);
        if (strlen($doc) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $doc);
        }
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $doc);
    }

    // Accessor: endereço completo
    public function getEnderecoCompletoAttribute(): string
    {
        $partes = array_filter([
            $this->logradouro,
            $this->numero,
            $this->complemento,
            $this->bairro,
            $this->cidade,
            $this->estado,
        ]);
        return implode(', ', $partes);
    }
}