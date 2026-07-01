<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lancamento extends Model
{
    protected $fillable = [
        'viagem_id',
        'tipo',
        'descricao',
        'valor',
        'data_lancamento',
        'comprovante',
        'observacao',
    ];

    protected $casts = [
        'data_lancamento' => 'date',
        'valor'           => 'decimal:2',
    ];

    // Lançamento pertence a uma viagem
    public function viagem()
    {
        return $this->belongsTo(Viagem::class);
    }

    // Após salvar, recalcula os totais da viagem
    protected static function booted(): void
    {
        static::saved(function ($lancamento) {
            $lancamento->viagem->recalcularTotais();
        });

        static::deleted(function ($lancamento) {
            $lancamento->viagem->recalcularTotais();
        });
    }
}