<?php

namespace App\Models\Concerns;

use App\Models\Empresa;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToEmpresa
{
    public static function bootBelongsToEmpresa(): void
    {
        static::addGlobalScope('empresa', function (Builder $query) {
            if ($empresaId = TenantContext::id()) {
                $query->where($query->getModel()->getTable() . '.empresa_id', $empresaId);
            }
        });

        static::creating(function ($model) {
            // array_key_exists (não "!empresa_id"): precisa distinguir "não veio
            // preenchido" de "veio preenchido como null de propósito" — é o caso
            // do super admin da plataforma, que não pertence a nenhuma empresa.
            if (! array_key_exists('empresa_id', $model->getAttributes())) {
                $model->empresa_id = TenantContext::id();
            }
        });
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
