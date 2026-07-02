<?php

namespace App\Notifications;

use App\Models\Viagem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ViagemEncerradaNotification extends Notification
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
            ->subject("Viagem #{$viagem->id} encerrada — acerto disponível")
            ->greeting("Olá, {$viagem->motorista->nome}!")
            ->line("A viagem #{$viagem->id} ({$viagem->origem} → {$viagem->destino}) foi encerrada.")
            ->line('Valor do frete: R$ ' . number_format($viagem->valor_frete, 2, ',', '.'))
            ->line('Sua comissão: R$ ' . number_format($viagem->valor_motorista, 2, ',', '.'))
            ->line('Descontos: R$ ' . number_format($viagem->total_descontos, 2, ',', '.'))
            ->line('Saldo final: R$ ' . number_format($viagem->saldo_motorista, 2, ',', '.'))
            ->line('Qualquer dúvida, entre em contato com a transportadora.');
    }
}
