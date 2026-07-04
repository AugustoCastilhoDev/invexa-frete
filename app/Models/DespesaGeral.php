<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DespesaGeral extends Model
{
    use BelongsToEmpresa, HasFactory, TracksUser;

    protected $table = 'despesas_gerais';

    protected $fillable = [
        'categoria',
        'descricao',
        'valor',
        'data_despesa',
        'recorrente',
        'observacao',
    ];

    protected $casts = [
        'data_despesa' => 'date',
        'valor'        => 'decimal:2',
        'recorrente'   => 'boolean',
    ];

    public function scopeNoPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_despesa', [$dataInicio, $dataFim]);
    }

    public function getCategoriaFormatadaAttribute(): string
    {
        return match ($this->categoria) {
            'aluguel'   => 'Aluguel',
            'salarios'  => 'Salários',
            'contas'    => 'Contas (água, luz, internet...)',
            'seguro'    => 'Seguro',
            'impostos'  => 'Impostos',
            'marketing' => 'Marketing',
            'outros'    => 'Outros',
            default     => $this->categoria,
        };
    }
}
