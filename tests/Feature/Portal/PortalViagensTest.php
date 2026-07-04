<?php

namespace Tests\Feature\Portal;

use App\Models\Motorista;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalViagensTest extends TestCase
{
    use RefreshDatabase;

    public function test_motorista_ve_apenas_as_proprias_viagens_na_listagem(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $outroMotorista = Motorista::factory()->comAcessoPortal()->create();

        $minhaViagem = Viagem::factory()->create(['motorista_id' => $motorista->id]);
        Viagem::factory()->create(['motorista_id' => $outroMotorista->id]);

        $response = $this->actingAs($motorista, 'motorista')->get(route('portal.viagens.index'));

        $response->assertOk();
        $response->assertViewHas('viagens', function ($viagens) use ($minhaViagem) {
            return $viagens->total() === 1 && $viagens->first()->is($minhaViagem);
        });
    }

    public function test_motorista_pode_ver_detalhe_da_propria_viagem(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $viagem = Viagem::factory()->create(['motorista_id' => $motorista->id]);

        $response = $this->actingAs($motorista, 'motorista')->get(route('portal.viagens.show', $viagem));

        $response->assertOk();
        $response->assertSee($viagem->origem);
    }

    public function test_motorista_nao_pode_ver_viagem_de_outro_motorista(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $outroMotorista = Motorista::factory()->comAcessoPortal()->create();
        $viagemDeOutro = Viagem::factory()->create(['motorista_id' => $outroMotorista->id]);

        $response = $this->actingAs($motorista, 'motorista')->get(route('portal.viagens.show', $viagemDeOutro));

        $response->assertForbidden();
    }

    public function test_motorista_nao_pode_baixar_comprovante_de_viagem_de_outro_motorista(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $outroMotorista = Motorista::factory()->comAcessoPortal()->create();
        $viagemDeOutro = Viagem::factory()->create(['motorista_id' => $outroMotorista->id]);

        $response = $this->actingAs($motorista, 'motorista')->get(route('portal.viagens.comprovante', $viagemDeOutro));

        $response->assertForbidden();
    }

    public function test_motorista_baixa_o_proprio_comprovante_em_pdf(): void
    {
        $motorista = Motorista::factory()->comAcessoPortal()->create();
        $viagem = Viagem::factory()->create(['motorista_id' => $motorista->id]);

        $response = $this->actingAs($motorista, 'motorista')->get(route('portal.viagens.comprovante', $viagem));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
