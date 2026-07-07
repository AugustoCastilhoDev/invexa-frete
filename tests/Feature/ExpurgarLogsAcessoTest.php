<?php

namespace Tests\Feature;

use App\Models\LogAcesso;
use App\Models\Motorista;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ExpurgarLogsAcessoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('lgpd.retencao_meses.logs_acesso', 12);
    }

    private function criarLog(int $mesesAtras): LogAcesso
    {
        $user = User::factory()->create();

        $log = new LogAcesso([
            'autenticavel_type' => User::class,
            'autenticavel_id'   => $user->id,
            'guard'             => 'web',
            'ip'                => '127.0.0.1',
        ]);
        $log->empresa_id = $user->empresa_id;
        $log->save();
        $log->forceFill(['created_at' => now()->subMonths($mesesAtras)])->saveQuietly();

        return $log;
    }

    public function test_apaga_logs_mais_antigos_que_o_prazo_de_retencao(): void
    {
        $antigo = $this->criarLog(13);

        $this->artisan('lgpd:expurgar-logs-acesso')->assertSuccessful();

        $this->assertDatabaseMissing('logs_acesso', ['id' => $antigo->id]);
    }

    public function test_nao_apaga_logs_dentro_do_prazo_de_retencao(): void
    {
        $recente = $this->criarLog(6);

        $this->artisan('lgpd:expurgar-logs-acesso');

        $this->assertDatabaseHas('logs_acesso', ['id' => $recente->id]);
    }

    public function test_dry_run_nao_apaga_nada(): void
    {
        $antigo = $this->criarLog(13);

        $this->artisan('lgpd:expurgar-logs-acesso --dry-run');

        $this->assertDatabaseHas('logs_acesso', ['id' => $antigo->id]);
    }

    public function test_respeita_prazo_configurado_via_config(): void
    {
        Config::set('lgpd.retencao_meses.logs_acesso', 3);

        $log = $this->criarLog(4);

        $this->artisan('lgpd:expurgar-logs-acesso');

        $this->assertDatabaseMissing('logs_acesso', ['id' => $log->id]);
    }

    public function test_expurgo_ignora_isolamento_multi_tenant_e_apaga_de_todas_as_empresas(): void
    {
        $motorista = Motorista::factory()->create();
        $log = new LogAcesso([
            'autenticavel_type' => Motorista::class,
            'autenticavel_id'   => $motorista->id,
            'guard'             => 'motorista',
            'ip'                => '127.0.0.1',
        ]);
        $log->empresa_id = $motorista->empresa_id;
        $log->save();
        $log->forceFill(['created_at' => now()->subMonths(13)])->saveQuietly();

        $this->artisan('lgpd:expurgar-logs-acesso');

        $this->assertDatabaseMissing('logs_acesso', ['id' => $log->id]);
    }
}
