<?php

namespace Tests\Feature\Portal;

use App\Models\Lancamento;
use App\Models\Motorista;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalLancamentosTest extends TestCase
{
    use RefreshDatabase;

    public function test_motorista_envia_lancamento_com_comprovante_e_fica_pendente(): void
    {
        Storage::fake('public');
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $viagem = Viagem::factory()->create(['motorista_id' => $motorista->id, 'status' => 'em_andamento']);

        $response = $this->actingAs($motorista, 'motorista')->post(route('portal.lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento na BR-116',
            'valor'           => 250,
            'data_lancamento' => now()->format('Y-m-d'),
            'comprovante'     => UploadedFile::fake()->create('recibo.jpg', 100),
        ]);

        $response->assertRedirect(route('portal.viagens.show', $viagem));

        $lancamento = Lancamento::firstOrFail();
        $this->assertEquals('pendente', $lancamento->status);
        $this->assertNotNull($lancamento->comprovante);
        Storage::disk('public')->assertExists($lancamento->comprovante);

        // não conta no total até ser aprovado
        $this->assertEquals(0, $viagem->fresh()->total_combustivel);
    }

    public function test_comprovante_e_obrigatorio(): void
    {
        Storage::fake('public');
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $viagem = Viagem::factory()->create(['motorista_id' => $motorista->id, 'status' => 'em_andamento']);

        $response = $this->actingAs($motorista, 'motorista')->post(route('portal.lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 250,
            'data_lancamento' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('comprovante');
        $this->assertDatabaseCount('lancamentos', 0);
    }

    public function test_motorista_nao_pode_lancar_despesa_em_viagem_de_outro_motorista(): void
    {
        Storage::fake('public');
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $outroMotorista = Motorista::factory()->create();
        $viagemDeOutro = Viagem::factory()->create(['motorista_id' => $outroMotorista->id, 'status' => 'em_andamento']);

        $response = $this->actingAs($motorista, 'motorista')->post(route('portal.lancamentos.store', $viagemDeOutro), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 250,
            'data_lancamento' => now()->format('Y-m-d'),
            'comprovante'     => UploadedFile::fake()->create('recibo.jpg', 100),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('lancamentos', 0);
    }

    public function test_motorista_nao_pode_lancar_despesa_em_viagem_encerrada(): void
    {
        Storage::fake('public');
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $viagem = Viagem::factory()->create(['motorista_id' => $motorista->id, 'status' => 'encerrada']);

        $response = $this->actingAs($motorista, 'motorista')->post(route('portal.lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 250,
            'data_lancamento' => now()->format('Y-m-d'),
            'comprovante'     => UploadedFile::fake()->create('recibo.jpg', 100),
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseCount('lancamentos', 0);
    }
}
