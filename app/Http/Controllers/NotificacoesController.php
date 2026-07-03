<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificacoesController extends Controller
{
    public function marcarComoLida(Request $request, string $notificacao)
    {
        $notificacao = $request->user()->notifications()->findOrFail($notificacao);
        $notificacao->markAsRead();

        return redirect($notificacao->data['url'] ?? route('dashboard'));
    }

    public function marcarTodasComoLidas(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
