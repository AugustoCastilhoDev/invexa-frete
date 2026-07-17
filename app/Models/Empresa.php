<?php

namespace App\Models;

use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory, TracksUser;

    protected $fillable = [
        'nome',
        'cnpj',
        'status',
        'limite_veiculos',
        'plano',
        'ciclo_cobranca',
        'asaas_customer_id',
        'asaas_subscription_id',
        'asaas_status',
        'asaas_last_event_at',
        'focus_nfe_ativo',
        'focus_nfe_ambiente',
        'focus_nfe_empresa_id',
        'focus_nfe_token',
        'focus_nfe_status',
        'focus_nfe_certificado_path',
        'focus_nfe_certificado_senha',
        'focus_nfe_certificado_validade',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'municipio',
        'codigo_municipio',
        'uf',
        'telefone',
        'inscricao_estadual',
        'rntrc',
        'regime_tributario',
        'cfop_padrao',
        'icms_situacao_tributaria',
        'icms_aliquota',
    ];

    protected $casts = [
        'asaas_last_event_at' => 'datetime',
        'focus_nfe_ativo' => 'boolean',
        'focus_nfe_token' => 'encrypted',
        'focus_nfe_certificado_senha' => 'encrypted',
        'focus_nfe_certificado_validade' => 'date',
        'icms_aliquota' => 'decimal:2',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    // Matriz/filiais desta empresa — cada uma com CNPJ/IE/endereço próprios
    // pra emissão fiscal, mas compartilhando frota/usuários/limite (não são
    // tenants separados).
    public function unidades()
    {
        return $this->hasMany(Unidade::class);
    }

    // null = sem limite (ilimitado)
    public function limiteVeiculosAtingido(): bool
    {
        if ($this->limite_veiculos === null) {
            return false;
        }

        return $this->veiculos()->withoutGlobalScope('empresa')->contamParaLimite()->count() >= $this->limite_veiculos;
    }

    public function pagamentoEmAtraso(): bool
    {
        return $this->asaas_status === 'PAYMENT_OVERDUE';
    }

    /**
     * Traduz o status bruto do Asaas (nome do evento do webhook, ou "em_trial"
     * logo após criar a assinatura) num rótulo e cor prontos para exibir,
     * já que o valor cru (ex.: "PAYMENT_OVERDUE") não é amigável ao usuário.
     */
    public function situacaoCobranca(): array
    {
        return match ($this->asaas_status) {
            null => ['label' => 'Sem assinatura', 'classe' => 'bg-secondary', 'icone' => 'bi-dash-circle'],
            'em_trial' => ['label' => 'Em trial', 'classe' => 'bg-info text-dark', 'icone' => 'bi-hourglass-split'],
            'PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED' => ['label' => 'Em dia', 'classe' => 'bg-success', 'icone' => 'bi-check-circle'],
            'PAYMENT_OVERDUE' => ['label' => 'Atrasado', 'classe' => 'bg-danger', 'icone' => 'bi-exclamation-triangle'],
            'PAYMENT_REFUNDED' => ['label' => 'Reembolsado', 'classe' => 'bg-warning text-dark', 'icone' => 'bi-arrow-counterclockwise'],
            default => ['label' => 'Pendente', 'classe' => 'bg-secondary', 'icone' => 'bi-question-circle'],
        };
    }
}
