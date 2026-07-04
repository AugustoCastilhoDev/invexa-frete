<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manutencao extends Model
{
    use BelongsToEmpresa, HasFactory, TracksUser;

    protected $table = 'manutencoes';

    protected $fillable = [
        'veiculo_id',
        'tipo',
        'descricao',
        'data_manutencao',
        'km_veiculo',
        'valor',
        'proxima_manutencao_data',
        'proxima_manutencao_km',
        'status',
        'observacao',
    ];

    protected $casts = [
        'data_manutencao'         => 'date',
        'proxima_manutencao_data' => 'date',
        'valor'                   => 'decimal:2',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    // Entre as manutenções mais recentes de cada veículo (uma por veículo),
    // as que têm próxima manutenção prevista dentro de $dias.
    public function scopeProximasVencendo($query, int $dias = 30)
    {
        return $query->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')
                    ->from('manutencoes')
                    ->groupBy('veiculo_id');
            })
            ->whereNotNull('proxima_manutencao_data')
            ->where('proxima_manutencao_data', '<=', now()->addDays($dias));
    }

    // Mantém o status do veículo sincronizado com a manutenção mais recente
    protected static function booted(): void
    {
        static::saved(function (Manutencao $manutencao) {
            $manutencao->sincronizarStatusVeiculo();
        });

        static::deleted(function (Manutencao $manutencao) {
            $manutencao->sincronizarStatusVeiculo();
        });
    }

    private function sincronizarStatusVeiculo(): void
    {
        $veiculo = $this->veiculo;

        if (! $veiculo) {
            return;
        }

        $emAndamento = Manutencao::where('veiculo_id', $veiculo->id)
            ->where('status', 'em_andamento')
            ->exists();

        if ($emAndamento && $veiculo->status !== 'manutencao') {
            $veiculo->update(['status' => 'manutencao']);
        } elseif (! $emAndamento && $veiculo->status === 'manutencao') {
            $veiculo->update(['status' => 'ativo']);
        }
    }
}
