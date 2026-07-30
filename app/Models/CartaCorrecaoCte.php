<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\RegistraAuditoria;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartaCorrecaoCte extends Model
{
    use BelongsToEmpresa, HasFactory, RegistraAuditoria, TracksUser;

    protected $table = 'cartas_correcao_cte';

    protected $fillable = [
        'emissao_fiscal_id',
        'grupo_corrigido',
        'campo_corrigido',
        'valor_corrigido',
        'numero_item_grupo_corrigido',
        'numero_carta_correcao',
        'status_sefaz',
        'mensagem_sefaz',
        'caminho_xml',
    ];

    public function emissaoFiscal()
    {
        return $this->belongsTo(EmissaoFiscal::class);
    }
}
