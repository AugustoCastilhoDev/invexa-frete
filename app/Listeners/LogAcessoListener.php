<?php

namespace App\Listeners;

use App\Models\LogAcesso;
use Illuminate\Auth\Events\Login;

class LogAcessoListener
{
    public function handle(Login $event): void
    {
        $log = new LogAcesso([
            'autenticavel_type' => get_class($event->user),
            'autenticavel_id'   => $event->user->getKey(),
            'guard'             => $event->guard,
            'ip'                => request()->ip(),
            'user_agent'        => request()->userAgent(),
        ]);

        $log->empresa_id = $event->user->empresa_id ?? null;
        $log->save();
    }
}
