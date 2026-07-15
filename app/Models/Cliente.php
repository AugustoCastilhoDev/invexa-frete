<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\TracksDeletingUser;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use BelongsToEmpresa, HasFactory, SoftDeletes, TracksUser, TracksDeletingUser;

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
        'codigo_municipio',
        'tabela_frete',
        'observacoes',
        'status',
    ];

    protected $casts = [
        'tabela_frete' => 'decimal:2',
        'anonymized_at' => 'datetime',
    ];

    // Cliente tem muitas viagens
    public function viagens()
    {
        return $this->hasMany(Viagem::class);
    }

    // Accessor: documento formatado. Usa posição (não \d) para também suportar
    // o CNPJ alfanumérico da Receita Federal (raiz+ordem podem ter letras;
    // só os 2 dígitos verificadores finais continuam numéricos) — um CNPJ
    // desse tipo não bate mais com um regex \d{14}.
    public function getDocumentoFormatadoAttribute(): string
    {
        $doc = preg_replace('/[^A-Za-z0-9]/', '', (string) $this->cpf_cnpj);

        if (strlen($doc) === 11) {
            return substr($doc, 0, 3).'.'.substr($doc, 3, 3).'.'.substr($doc, 6, 3).'-'.substr($doc, 9, 2);
        }

        if (strlen($doc) === 14) {
            return substr($doc, 0, 2).'.'.substr($doc, 2, 3).'.'.substr($doc, 5, 3).'/'.substr($doc, 8, 4).'-'.substr($doc, 12, 2);
        }

        return (string) $this->cpf_cnpj;
    }

    // Accessor: documento mascarado (CPF de pessoa física é dado pessoal; CNPJ não)
    public function getDocumentoMascaradoAttribute(): string
    {
        if ($this->tipo_pessoa !== 'fisica') {
            return $this->documento_formatado;
        }

        $doc = preg_replace('/[^A-Za-z0-9]/', '', (string) $this->cpf_cnpj);

        if (strlen($doc) !== 11) {
            return $this->documento_formatado;
        }

        return substr($doc, 0, 3) . '.***.***-' . substr($doc, 9, 2);
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