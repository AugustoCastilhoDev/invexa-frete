<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\TracksDeletingUser;
use App\Models\Concerns\TracksUser;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Motorista extends Model implements AuthenticatableContract
{
    use Authenticatable, BelongsToEmpresa, HasFactory, SoftDeletes, TracksUser, TracksDeletingUser;

    protected $fillable = [
        'nome',
        'cpf',
        'cnh',
        'categoria_cnh',
        'validade_cnh',
        'telefone',
        'email',
        'percentual_comissao',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'validade_cnh' => 'date',
        'percentual_comissao' => 'decimal:2',
        'anonymized_at' => 'datetime',
        'portal_ativo' => 'boolean',
        'cnh' => 'encrypted',
    ];

    public function hasPortalAtivo(): bool
    {
        return $this->portal_ativo && $this->password !== null;
    }

    // Um motorista tem muitas viagens
    public function viagens()
    {
        return $this->hasMany(Viagem::class);
    }

    // Motoristas ativos com CNH já vencida ou vencendo nos próximos $dias
    public function scopeCnhVencendo($query, int $dias = 30)
    {
        return $query->where('status', 'ativo')
            ->whereNotNull('validade_cnh')
            ->where('validade_cnh', '<=', now()->addDays($dias));
    }

    // Accessor: CPF mascarado (mantém apenas os 3 primeiros e 2 últimos dígitos)
    public function getCpfMascaradoAttribute(): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $this->cpf);

        if (strlen($digitos) !== 11) {
            return $this->cpf;
        }

        return substr($digitos, 0, 3) . '.***.***-' . substr($digitos, 9, 2);
    }

    // Accessor: CNH mascarada (mantém apenas os 2 primeiros e 2 últimos dígitos)
    public function getCnhMascaradaAttribute(): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $this->cnh);
        $tamanho = strlen($digitos);

        if ($tamanho === 0) {
            return null;
        }

        if ($tamanho <= 4) {
            return str_repeat('*', $tamanho);
        }

        return substr($digitos, 0, 2) . str_repeat('*', $tamanho - 4) . substr($digitos, -2);
    }
}