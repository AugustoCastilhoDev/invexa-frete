<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class LogAcesso extends Model
{
    use BelongsToEmpresa;

    protected $table = 'logs_acesso';

    protected $fillable = [
        'autenticavel_type',
        'autenticavel_id',
        'guard',
        'ip',
        'user_agent',
    ];

    public function autenticavel()
    {
        return $this->morphTo();
    }
}
