<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class MdfePendenteDeEncerramentoNotification extends Notification
{
    use Queueable;

    // @param Collection<int, \App\Models\EmissaoFiscal> $emissoes
    public function __construct(public Collection $emissoes)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mensagem = (new MailMessage)
            ->subject($this->emissoes->count() === 1
                ? 'MDF-e pendente de encerramento'
                : "{$this->emissoes->count()} MDF-e's pendentes de encerramento")
            ->greeting('Viagem concluída, MDF-e ainda aberto na SEFAZ')
            ->line('As viagens abaixo já foram encerradas operacionalmente, mas o MDF-e continua "autorizado" — encerre para regularizar perante a SEFAZ.');

        foreach ($this->emissoes as $emissao) {
            $viagem = $emissao->viagem;
            $mensagem->line("• Viagem #{$viagem->id} ({$viagem->origem} → {$viagem->destino}) · veículo {$viagem->veiculo?->placa} · autorizado em " . $emissao->autorizado_em?->format('d/m/Y'));
        }

        return $mensagem
            ->action('Ver MDF-e pendentes', route('emissoes-fiscais.mdfe', ['status' => 'autorizado']))
            ->line('Este é um aviso automático do Invexa Frete — repete todo dia até o MDF-e ser encerrado.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'titulo'   => $this->emissoes->count() === 1
                ? 'MDF-e pendente de encerramento'
                : "{$this->emissoes->count()} MDF-e's pendentes de encerramento",
            'mensagem' => $this->emissoes->map(fn ($e) => "Viagem #{$e->viagem_id}")->implode(', '),
            'url'      => route('emissoes-fiscais.mdfe', ['status' => 'autorizado']),
        ];
    }
}
