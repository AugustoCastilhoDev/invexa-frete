<?php

namespace Tests\Feature\Viagens;

use App\Models\User;
use App\Models\Viagem;
use App\Notifications\ViagemAguardandoAcertoNotification;
use App\Notifications\ViagemEncerradaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificacoesViagemTest extends TestCase
{
    use RefreshDatabase;

    public function test_viagem_entrar_em_aguardando_acerto_notifica_admins_ativos(): void
    {
        Notification::fake();

        $admin           = User::factory()->admin()->create();
        $adminInativo     = User::factory()->admin()->inativo()->create();
        $operador         = User::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        $viagem = Viagem::factory()->create(['status' => 'aberta']);
        $viagem->update(['status' => 'aguardando_acerto']);

        Notification::assertSentTo($admin, ViagemAguardandoAcertoNotification::class);
        Notification::assertNotSentTo($adminInativo, ViagemAguardandoAcertoNotification::class);
        Notification::assertNotSentTo($operador, ViagemAguardandoAcertoNotification::class);
    }

    public function test_encerrar_viagem_notifica_motorista_por_email(): void
    {
        Notification::fake();
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'aguardando_acerto']);

        $this->patch(route('viagens.encerrar', $viagem));

        Notification::assertSentOnDemand(
            ViagemEncerradaNotification::class,
            function ($notification, $channels, $notifiable) use ($viagem) {
                return $notifiable->routes['mail'] === $viagem->motorista->email;
            }
        );
    }

    public function test_recalcular_totais_sem_mudar_status_nao_reenvia_notificacao(): void
    {
        Notification::fake();
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'encerrada']);
        $viagem->recalcularTotais();

        Notification::assertNothingSent();
    }

    public function test_motorista_sem_email_nao_gera_erro_ao_encerrar(): void
    {
        Notification::fake();
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['status' => 'aguardando_acerto']);
        $viagem->motorista()->update(['email' => null]);

        $response = $this->patch(route('viagens.encerrar', $viagem));

        $response->assertRedirect(route('viagens.show', $viagem));
        Notification::assertNothingSent();
    }
}
