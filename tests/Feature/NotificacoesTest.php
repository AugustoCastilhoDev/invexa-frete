<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Viagem;
use App\Notifications\ViagemAguardandoAcertoNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacoesTest extends TestCase
{
    use RefreshDatabase;

    public function test_viagem_aguardando_acerto_gera_notificacao_no_banco_para_cada_admin(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $viagem = Viagem::factory()->create(['status' => 'aberta']);
        $viagem->update(['status' => 'aguardando_acerto']);

        $this->assertCount(1, $admin1->fresh()->unreadNotifications);
        $this->assertCount(1, $admin2->fresh()->unreadNotifications);

        $dados = $admin1->fresh()->unreadNotifications->first()->data;
        $this->assertStringContainsString("Viagem #{$viagem->id}", $dados['titulo']);
        $this->assertEquals(route('viagens.show', $viagem), $dados['url']);
    }

    public function test_um_admin_marcar_como_lida_nao_afeta_a_notificacao_de_outro_admin(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $viagem = Viagem::factory()->create(['status' => 'aberta']);
        $viagem->update(['status' => 'aguardando_acerto']);

        $notificacaoAdmin1 = $admin1->fresh()->unreadNotifications->first();

        $this->actingAs($admin1)
            ->post(route('notificacoes.ler', $notificacaoAdmin1->id))
            ->assertRedirect(route('viagens.show', $viagem));

        $this->assertCount(0, $admin1->fresh()->unreadNotifications);
        $this->assertCount(1, $admin2->fresh()->unreadNotifications);
    }

    public function test_admin_nao_pode_marcar_como_lida_notificacao_de_outro_admin(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $viagem = Viagem::factory()->create(['status' => 'aberta']);
        $viagem->update(['status' => 'aguardando_acerto']);

        $notificacaoAdmin2 = $admin2->fresh()->unreadNotifications->first();

        $this->actingAs($admin1)
            ->post(route('notificacoes.ler', $notificacaoAdmin2->id))
            ->assertNotFound();

        $this->assertCount(1, $admin2->fresh()->unreadNotifications);
    }

    public function test_marcar_todas_como_lidas(): void
    {
        $admin = User::factory()->admin()->create();

        Viagem::factory()->create(['status' => 'aberta'])->update(['status' => 'aguardando_acerto']);
        Viagem::factory()->create(['status' => 'aberta'])->update(['status' => 'aguardando_acerto']);

        $this->assertCount(2, $admin->fresh()->unreadNotifications);

        $this->actingAs($admin)
            ->post(route('notificacoes.ler-todas'))
            ->assertRedirect();

        $this->assertCount(0, $admin->fresh()->unreadNotifications);
    }

    public function test_sino_de_notificacoes_aparece_no_layout_sem_erro_mesmo_sem_notificacoes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Nenhuma notificação pendente');
    }
}
