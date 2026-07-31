<?php

namespace Tests\Feature;

use App\Models\EmissaoFiscal;
use App\Notifications\SaudeSistemaAlertaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerificarSaudeSistemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_sistema_saudavel_nao_envia_alerta(): void
    {
        Notification::fake();

        $this->artisan('sistema:verificar-saude')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_jobs_falhados_recentes_geram_alerta(): void
    {
        Notification::fake();

        DB::table('failed_jobs')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'teste',
            'failed_at' => now(),
        ]);

        $this->artisan('sistema:verificar-saude')->assertExitCode(1);

        Notification::assertSentOnDemand(
            SaudeSistemaAlertaNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === config('backup.notifications.mail.to')
        );
    }

    public function test_jobs_falhados_antigos_nao_geram_alerta(): void
    {
        Notification::fake();

        DB::table('failed_jobs')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'teste',
            'failed_at' => now()->subHours(3),
        ]);

        $this->artisan('sistema:verificar-saude')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_emissao_fiscal_com_erro_recente_gera_alerta(): void
    {
        Notification::fake();

        EmissaoFiscal::factory()->create(['status' => 'erro_autorizacao']);

        $this->artisan('sistema:verificar-saude')->assertExitCode(1);

        Notification::assertSentOnDemand(SaudeSistemaAlertaNotification::class);
    }

    public function test_emissao_fiscal_com_erro_antiga_nao_gera_alerta(): void
    {
        Notification::fake();

        $emissao = EmissaoFiscal::factory()->create(['status' => 'erro_autorizacao']);
        $emissao->timestamps = false;
        $emissao->updated_at = now()->subHours(3);
        $emissao->saveQuietly();

        $this->artisan('sistema:verificar-saude')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_emissao_fiscal_autorizada_nao_gera_alerta(): void
    {
        Notification::fake();

        EmissaoFiscal::factory()->autorizada()->create();

        $this->artisan('sistema:verificar-saude')->assertExitCode(0);

        Notification::assertNothingSent();
    }
}
