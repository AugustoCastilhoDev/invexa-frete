<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\RegistraAuditoria;
use App\Models\Concerns\TracksDeletingUser;
use App\Models\Concerns\TracksUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends Model
{
    use BelongsToEmpresa, HasFactory, RegistraAuditoria, SoftDeletes, TracksUser, TracksDeletingUser;

    protected $fillable = [
        'placa',
        'modelo',
        'marca',
        'ano',
        'tipo',
        'tipo_carroceria',
        'renavam',
        'chassi',
        'validade_documento',
        'cavalo_id',
        'capacidade_kg',
        'tara_kg',
        'status',
        'vinculo',
    ];

    protected $casts = [
        'validade_documento' => 'date',
    ];

    public const TIPOS_CAVALO = ['cavalo_simples', 'cavalo_trucado'];

    public const TIPOS = [
        'truck'           => 'Truck (Chassi Rígido)',
        'bitruck'         => 'Bitruck (Chassi Rígido)',
        'cavalo_simples'  => 'Cavalo Mecânico Simples (4x2)',
        'cavalo_trucado'  => 'Cavalo Mecânico Trucado (6x2/6x4)',
        'carreta'         => 'Carreta (Semirreboque)',
        'bitrem_rodotrem' => 'Bitrem/Rodotrem (Combinação)',
        'van'             => 'Van',
        'utilitario'      => 'Utilitário',
        'outro'           => 'Outro',
    ];

    public const TIPOS_CARROCERIA = [
        'bau_sider'         => 'Baú/Sider',
        'graneleiro'        => 'Graneleiro/Grade Baixa',
        'cacamba'           => 'Caçamba',
        'prancha_container' => 'Prancha/Porta-Contêiner',
    ];

    // Um veículo tem muitas viagens
    public function viagens()
    {
        return $this->hasMany(Viagem::class);
    }

    // Um veículo tem muitas manutenções
    public function manutencoes()
    {
        return $this->hasMany(Manutencao::class)->orderByDesc('data_manutencao');
    }

    // Cavalo mecânico ao qual esta carreta está vinculada
    public function cavalo()
    {
        return $this->belongsTo(Veiculo::class, 'cavalo_id');
    }

    // Carreta(s) vinculada(s) a este cavalo mecânico
    public function carretas()
    {
        return $this->hasMany(Veiculo::class, 'cavalo_id');
    }

    // Carreta vinculada que está disponível pra uso agora (não em manutenção/inativa)
    // — usada como sugestão de padrão nos formulários de Programação/Viagem, sem
    // travar a escolha de uma diferente se essa estiver indisponível.
    public function carretaVinculadaDisponivel(): ?self
    {
        return $this->carretas()->where('status', 'ativo')->first();
    }

    public function scopeEmManutencao($query)
    {
        return $query->where('status', 'manutencao');
    }

    // Só os tipos que tracionam (podem puxar uma carreta) — truck/bitruck são
    // chassi rígido, não entram aqui.
    public function scopeCavalos($query)
    {
        return $query->whereIn('tipo', self::TIPOS_CAVALO);
    }

    // Conjunto (cavalo + carreta) conta como 1 veículo no limite do plano:
    // a carreta só entra na contagem separadamente enquanto não está vinculada a um cavalo.
    public function scopeContamParaLimite($query)
    {
        return $query->where(function ($q) {
            $q->where('tipo', '!=', 'carreta')->orWhereNull('cavalo_id');
        });
    }

    public function getVinculoFormatadoAttribute(): string
    {
        return $this->vinculo === 'agregado' ? 'Agregado' : 'Frota Própria';
    }

    public function getTipoFormatadoAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst($this->tipo);
    }

    public function getTipoCarroceriaFormatadoAttribute(): ?string
    {
        return $this->tipo_carroceria ? (self::TIPOS_CARROCERIA[$this->tipo_carroceria] ?? $this->tipo_carroceria) : null;
    }
}