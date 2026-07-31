<?php

namespace App\Notifications;

use App\Models\Viagem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComprovanteAcertoAssinadoNotification extends Notification
{
    use Queueable;

    public function __construct(public Viagem $viagem, public string $comprovantePdf)
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
            ->subject("Comprovante de acerto assinado — Viagem #{$viagem->id}")
            ->greeting("Olá, {$viagem->motorista->nome}!")
            ->line("Segue em anexo o comprovante do acerto da viagem #{$viagem->id} ({$viagem->origem} → {$viagem->destino}), assinado agora.")
            ->line('Saldo a receber: R$ ' . number_format($viagem->saldo_motorista, 2, ',', '.'))
            ->line('Guarde este e-mail — ele contém a cópia do que foi assinado.')
            ->attachData($this->comprovantePdf, "acerto-viagem-{$viagem->id}.pdf", ['mime' => 'application/pdf']);
    }
}
