<?php

namespace Tests\Feature;

use App\Models\EmissaoFiscal;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Viagem;
use App\Notifications\MdfePendenteDeEncerramentoNotification;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LembrarMdfePendenteDeEncerramentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_nenhum_mdfe_pendente_nao_envia_notificacao(): void
    {
        Notification::fake();

        $this->artisan('mdfe:lembrar-pendente-encerramento')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_mdfe_autorizado_com_viagem_encerrada_notifica_admins_ativos(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create(['status' => 'ativo']);
        $adminInativo = User::factory()->admin()->create(['status' => 'inativo']);
        $operador = User::factory()->create(['status' => 'ativo']);

        $viagem = Viagem::factory()->encerrada()->create();
        EmissaoFiscal::factory()->autorizada()->create([
            'tipo' => 'mdfe',
            'viagem_id' => $viagem->id,
        ]);

        $this->artisan('mdfe:lembrar-pendente-encerramento')->assertExitCode(0);

        Notification::assertSentTo($admin, MdfePendenteDeEncerramentoNotification::class);
        Notification::assertNotSentTo($adminInativo, MdfePendenteDeEncerramentoNotification::class);
        Notification::assertNotSentTo($operador, MdfePendenteDeEncerramentoNotification::class);
    }

    public function test_mdfe_ja_encerrado_nao_gera_lembrete(): void
    {
        Notification::fake();

        User::factory()->admin()->create(['status' => 'ativo']);
        $viagem = Viagem::factory()->encerrada()->create();
        EmissaoFiscal::factory()->encerrada()->create(['viagem_id' => $viagem->id]);

        $this->artisan('mdfe:lembrar-pendente-encerramento')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_mdfe_de_viagem_ainda_em_andamento_nao_gera_lembrete(): void
    {
        Notification::fake();

        User::factory()->admin()->create(['status' => 'ativo']);
        $viagem = Viagem::factory()->create(['status' => 'em_andamento']);
        EmissaoFiscal::factory()->autorizada()->create([
            'tipo' => 'mdfe',
            'viagem_id' => $viagem->id,
        ]);

        $this->artisan('mdfe:lembrar-pendente-encerramento')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_cte_autorizado_nao_gera_lembrete_de_mdfe(): void
    {
        Notification::fake();

        User::factory()->admin()->create(['status' => 'ativo']);
        $viagem = Viagem::factory()->encerrada()->create();
        EmissaoFiscal::factory()->autorizada()->create([
            'tipo' => 'cte',
            'viagem_id' => $viagem->id,
        ]);

        $this->artisan('mdfe:lembrar-pendente-encerramento')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_agrupa_por_empresa_e_nao_notifica_admin_de_outra_empresa(): void
    {
        Notification::fake();

        $adminDaEmpresa = User::factory()->admin()->create(['status' => 'ativo']);
        $viagem = Viagem::factory()->encerrada()->create();
        EmissaoFiscal::factory()->autorizada()->create([
            'tipo' => 'mdfe',
            'viagem_id' => $viagem->id,
        ]);

        $outraEmpresa = Empresa::factory()->create();
        $adminDeOutraEmpresa = User::factory()->admin()->create([
            'status' => 'ativo',
            'empresa_id' => $outraEmpresa->id,
        ]);

        $this->artisan('mdfe:lembrar-pendente-encerramento')->assertExitCode(0);

        Notification::assertSentTo($adminDaEmpresa, MdfePendenteDeEncerramentoNotification::class);
        Notification::assertNotSentTo($adminDeOutraEmpresa, MdfePendenteDeEncerramentoNotification::class);
    }
}
