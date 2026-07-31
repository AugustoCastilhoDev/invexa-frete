<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Deliberadamente NÃO implementa ShouldQueue: isso avisa sobre problemas no
// próprio sistema (disco cheio, fila travada) — se enfileirasse e a fila for
// justamente o que está com problema, o alerta nunca sairia.
class SaudeSistemaAlertaNotification extends Notification
{
    public function __construct(private readonly array $alertas)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mensagem = (new MailMessage)
            ->subject('⚠️ Alerta de saúde do sistema — Invexa Frete')
            ->greeting('Atenção:');

        foreach ($this->alertas as $alerta) {
            $mensagem->line("• {$alerta}");
        }

        return $mensagem->line('Verifique /diagnostico no painel para mais detalhes.');
    }
}
