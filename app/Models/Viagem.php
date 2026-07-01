<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Viagem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'viagens';

    // Força o Laravel a reconhecer o parâmetro correto na rota
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    protected $fillable = [
        'motorista_id',
        'veiculo_id',
        'origem',
        'destino',
        'cliente_id',
        'data_saida',
        'data_retorno',
        'km_inicial',
        'km_final',
        'valor_frete',
        'percentual_motorista',
        'valor_motorista',
        'total_combustivel',
        'total_manutencao',
        'total_descontos',
        'valor_adiantamento',
        'adiantamento_descontavel',
        'saldo_motorista',
        'lucro_transportadora',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_saida'           => 'date',
        'data_retorno'         => 'date',
        'valor_frete'          => 'decimal:2',
        'percentual_motorista' => 'decimal:2',
        'valor_motorista'      => 'decimal:2',
        'total_combustivel'    => 'decimal:2',
        'total_manutencao'     => 'decimal:2',
        'total_descontos'      => 'decimal:2',
        'valor_adiantamento'   => 'decimal:2',
        'adiantamento_descontavel' => 'boolean',
        'saldo_motorista'      => 'decimal:2',
        'lucro_transportadora' => 'decimal:2',
    ];

    // Viagem pertence a um motorista
    public function motorista()
    {
        return $this->belongsTo(Motorista::class);
    }

    // Viagem pertence a um veículo
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    // Viagem pertence a um cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Viagem tem muitos lançamentos
    public function lancamentos()
    {
        return $this->hasMany(Lancamento::class);
    }

    // Viagem tem muitos descontos
    public function descontos()
    {
        return $this->hasMany(Desconto::class);
    }

    // Viagem tem muitos documentos
    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }
    // Acessor: KM rodados
    public function getKmRodadosAttribute(): int
    {
        if ($this->km_inicial && $this->km_final) {
            return $this->km_final - $this->km_inicial;
        }
        return 0;
    }

    // Recalcula todos os totais da viagem
    public function recalcularTotais(): void
{
    $this->total_combustivel = $this->lancamentos()
        ->where('tipo', 'combustivel')->sum('valor');

    $this->total_manutencao = $this->lancamentos()
        ->where('tipo', 'manutencao')->sum('valor');

    $this->total_descontos = $this->descontos()->sum('valor');

    $this->valor_motorista = round(
        ($this->valor_frete * $this->percentual_motorista) / 100, 2
    );

    $this->valor_adiantamento = $this->valor_adiantamento ?? 0;

    // Só desconta se adiantamento_descontavel for true
    $adiantamento = $this->adiantamento_descontavel
        ? $this->valor_adiantamento
        : 0;

    $this->saldo_motorista = round(
        $this->valor_motorista - $this->total_descontos - $adiantamento, 2
    );

    $this->lucro_transportadora = round(
        $this->valor_frete
        - $this->valor_motorista
        - $this->total_combustivel
        - $this->total_manutencao, 2
    );

    $this->save();
    }
}