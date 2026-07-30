<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Motorista;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_criar_viagem_gera_registro_de_auditoria(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create();

        $log = ActivityLog::where('subject_type', Viagem::class)
            ->where('subject_id', $viagem->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('viagens', $log->log_name);
    }

    public function test_editar_viagem_registra_apenas_os_campos_alterados(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $viagem = Viagem::factory()->create(['valor_frete' => 1000]);
        $viagem->update(['valor_frete' => 1500]);

        $log = ActivityLog::where('subject_type', Viagem::class)
            ->where('subject_id', $viagem->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(User::class, $log->causer_type);
        $this->assertEquals($user->id, $log->causer_id);
        $this->assertArrayHasKey('valor_frete', $log->changes()['attributes']);
        $this->assertArrayNotHasKey('created_at', $log->changes()['attributes'] ?? []);
    }

    public function test_excluir_cliente_registra_auditoria(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $cliente = Cliente::factory()->create();
        $cliente->delete();

        $log = ActivityLog::where('subject_type', Cliente::class)
            ->where('subject_id', $cliente->id)
            ->where('event', 'deleted')
            ->first();

        $this->assertNotNull($log);
    }

    public function test_dados_sensiveis_nao_aparecem_no_log_do_motorista(): void
    {
        $this->actingAs(User::factory()->create());

        $motorista = Motorista::factory()->create(['cpf' => '123.456.789-00', 'cnh' => '12345678900']);
        $motorista->update(['nome' => 'Novo Nome']);

        $log = ActivityLog::where('subject_type', Motorista::class)
            ->where('subject_id', $motorista->id)
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $propriedades = json_encode($log->properties);
        $this->assertStringNotContainsString('123.456.789-00', $propriedades);
        $this->assertStringNotContainsString('12345678900', $propriedades);
    }

    public function test_log_de_uma_empresa_nao_aparece_para_outra(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $userA = User::factory()->create(['empresa_id' => $empresaA->id]);
        $this->actingAs($userA);
        $viagemA = Viagem::factory()->create();

        $userB = User::factory()->create(['empresa_id' => $empresaB->id]);
        $this->actingAs($userB);
        $viagemB = Viagem::factory()->create();

        $this->actingAs($userA);
        $logsVisiveis = ActivityLog::where('subject_type', Viagem::class)->pluck('subject_id');

        $this->assertTrue($logsVisiveis->contains($viagemA->id));
        $this->assertFalse($logsVisiveis->contains($viagemB->id));
    }
}
