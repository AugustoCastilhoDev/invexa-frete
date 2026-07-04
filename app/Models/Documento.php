<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\HasUploadedFile;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use BelongsToEmpresa, HasFactory, HasUploadedFile, TracksUser;

    protected $fillable = [
        'viagem_id',
        'tipo',
        'numero',
        'chave_acesso',
        'serie',
        'data_emissao',
        'valor',
        'status',
        'arquivo',
        'observacao',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'valor'        => 'decimal:2',
    ];

    // Documento pertence a uma viagem
    public function viagem()
    {
        return $this->belongsTo(Viagem::class);
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    // Accessor: tipo formatado
    public function getTipoFormatadoAttribute(): string
    {
        return match($this->tipo) {
            'cte'    => 'CT-e',
            'mdfe'   => 'MDF-e',
            'nfe'    => 'NF-e',
            'outros' => 'Outros',
            default  => $this->tipo,
        };
    }

    // Accessor: badge de status
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'autorizado' => 'success',
            'cancelado'  => 'danger',
            default      => 'warning',
        };
    }

    // Accessor: URL para baixar o arquivo (assinada e temporária, se o disco for a nuvem)
    public function getArquivoUrlAttribute(): ?string
    {
        return $this->uploadedFileUrl($this->arquivo);
    }

    // Portal público oficial da SEFAZ para consultar a autenticidade pela chave de
    // acesso. NF-e e CT-e são só chave + captcha; MDF-e exige login gov.br (não tem
    // consulta pública simples equivalente até o momento).
    private const URL_CONSULTA_SEFAZ = [
        'nfe'  => 'https://www.nfe.fazenda.gov.br/portal/consultaRecaptcha.aspx',
        'cte'  => 'https://www.cte.fazenda.gov.br/portal/consultaRecaptcha.aspx',
        'mdfe' => 'https://dfe-portal.svrs.rs.gov.br/Mdfe/consulta',
    ];

    public function getUrlConsultaSefazAttribute(): ?string
    {
        if (! $this->chave_acesso) {
            return null;
        }

        return self::URL_CONSULTA_SEFAZ[$this->tipo] ?? null;
    }

    public function getExigeLoginGovBrAttribute(): bool
    {
        return $this->tipo === 'mdfe';
    }
}