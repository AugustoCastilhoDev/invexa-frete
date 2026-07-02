<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Motorista;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class LgpdAnonimizacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('lgpd.retencao_anos.motoristas', 5);
        Config::set('lgpd.retencao_anos.clientes', 5);
        Config::set('lgpd.retencao_anos.usuarios', 5);
    }

    public function test_anonimiza_motorista_excluido_ha_mais_tempo_que_a_retencao(): void
    {
        $motorista = Motorista::factory()->create(['cpf' => '12345678901']);
        $motorista->delete();
        $motorista->forceFill(['deleted_at' => now()->subYears(6)])->saveQuietly();

        $this->artisan('lgpd:anonimizar')->assertSuccessful();

        $motorista->refresh();
        $this->assertEquals('Motorista Anonimizado #' . $motorista->id, $motorista->nome);
        $this->assertEquals('ANON' . $motorista->id, $motorista->cpf);
        $this->assertNull($motorista->telefone);
        $this->assertNull($motorista->email);
        $this->assertNotNull($motorista->anonymized_at);
    }

    public function test_nao_anonimiza_motorista_excluido_dentro_do_prazo_de_retencao(): void
    {
        $motorista = Motorista::factory()->create(['nome' => 'Recém Excluído']);
        $motorista->delete();
        $motorista->forceFill(['deleted_at' => now()->subYears(1)])->saveQuietly();

        $this->artisan('lgpd:anonimizar');

        $motorista->refresh();
        $this->assertEquals('Recém Excluído', $motorista->nome);
        $this->assertNull($motorista->anonymized_at);
    }

    public function test_nao_anonimiza_motorista_ativo(): void
    {
        $motorista = Motorista::factory()->create(['nome' => 'Motorista Ativo']);

        $this->artisan('lgpd:anonimizar');

        $motorista->refresh();
        $this->assertEquals('Motorista Ativo', $motorista->nome);
    }

    public function test_anonimiza_cliente_pessoa_fisica_mas_nao_pessoa_juridica(): void
    {
        $fisica = Cliente::factory()->fisica()->create();
        $fisica->delete();
        $fisica->forceFill(['deleted_at' => now()->subYears(6)])->saveQuietly();

        $juridica = Cliente::factory()->create(); // pessoa jurídica
        $juridica->delete();
        $juridica->forceFill(['deleted_at' => now()->subYears(6)])->saveQuietly();

        $this->artisan('lgpd:anonimizar');

        $this->assertNotNull($fisica->fresh()->anonymized_at);
        $this->assertNull($juridica->fresh()->anonymized_at);
        $this->assertEquals('Cliente Anonimizado #' . $fisica->id, $fisica->fresh()->nome);
    }

    public function test_anonimiza_usuario_excluido_ha_mais_tempo_que_a_retencao(): void
    {
        $user = User::factory()->create(['name' => 'Fulano', 'email' => 'fulano@exemplo.com']);
        $user->delete();
        $user->forceFill(['deleted_at' => now()->subYears(6)])->saveQuietly();

        $this->artisan('lgpd:anonimizar');

        $user->refresh();
        $this->assertEquals('Usuário Anonimizado #' . $user->id, $user->name);
        $this->assertEquals('anon+' . $user->id . '@invexafrete.invalid', $user->email);
    }

    public function test_dry_run_nao_altera_nada(): void
    {
        $motorista = Motorista::factory()->create(['nome' => 'Vai Continuar Igual']);
        $motorista->delete();
        $motorista->forceFill(['deleted_at' => now()->subYears(6)])->saveQuietly();

        $this->artisan('lgpd:anonimizar --dry-run');

        $motorista->refresh();
        $this->assertEquals('Vai Continuar Igual', $motorista->nome);
        $this->assertNull($motorista->anonymized_at);
    }

    public function test_registro_ja_anonimizado_nao_e_reprocessado(): void
    {
        $motorista = Motorista::factory()->create();
        $motorista->delete();
        $motorista->forceFill([
            'deleted_at' => now()->subYears(10),
            'anonymized_at' => now()->subYear(),
            'nome' => 'Motorista Anonimizado #' . $motorista->id,
        ])->saveQuietly();

        $marcadoEm = $motorista->anonymized_at;

        $this->artisan('lgpd:anonimizar');

        $this->assertTrue($marcadoEm->equalTo($motorista->fresh()->anonymized_at));
    }

    public function test_respeita_prazo_de_retencao_configurado_via_config(): void
    {
        Config::set('lgpd.retencao_anos.motoristas', 1);

        $motorista = Motorista::factory()->create();
        $motorista->delete();
        $motorista->forceFill(['deleted_at' => now()->subYears(2)])->saveQuietly();

        $this->artisan('lgpd:anonimizar');

        $this->assertNotNull($motorista->fresh()->anonymized_at);
    }
}
