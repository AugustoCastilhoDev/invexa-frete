<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desconto extends Model
{
    protected $fillable = [
        'viagem_id',
        'tipo',
        'descricao',
        'valor',
        'data_desconto',
        'observacao',
    ];

    protected $casts = [
        'data_desconto' => 'date',
        'valor'         => 'decimal:2',
    ];

    // Desconto pertence a uma viagem
    public function viagem()
    {
        return $this->belongsTo(Viagem::class);
    }

    // Após salvar, recalcula os totais da viagem
    protected static function booted(): void
    {
        static::saved(function ($desconto) {
            $desconto->viagem->recalcularTotais();
        });

        static::deleted(function ($desconto) {
            $desconto->viagem->recalcularTotais();
        });
    }
}