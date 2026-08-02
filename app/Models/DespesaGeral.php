<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\RegistraAuditoria;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DespesaGeral extends Model
{
    use BelongsToEmpresa, HasFactory, RegistraAuditoria, TracksUser;

    protected $table = 'despesas_gerais';

    protected $fillable = [
        'categoria',
        'descricao',
        'valor',
        'data_despesa',
        'recorrente',
        'observacao',
        'veiculo_id',
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

    // Quando informado, esta despesa é custo direto de um veículo específico
    // (seguro, IPVA, financiamento) em vez de overhead corporativo rateado
    // entre a frota — ver CustoFrotaController.
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
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
