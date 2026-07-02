<?php

namespace App\Notifications;

use App\Models\Viagem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ViagemAguardandoAcertoNotification extends Notification
{
    use Queueable;

    public function __construct(public Viagem $viagem)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $viagem = $this->viagem;

        return (new MailMessage)
            ->subject("Viagem #{$viagem->id} aguardando acerto")
            ->greeting('Viagem pronta para acerto')
            ->line("A viagem #{$viagem->id} ({$viagem->origem} → {$viagem->destino}) está aguardando acerto com o motorista {$viagem->motorista->nome}.")
            ->line('Saldo a pagar: R$ ' . number_format($viagem->saldo_motorista, 2, ',', '.'))
            ->action('Ver viagem', route('viagens.show', $viagem))
            ->line('Este é um aviso automático do Invexa Frete.');
    }
}
