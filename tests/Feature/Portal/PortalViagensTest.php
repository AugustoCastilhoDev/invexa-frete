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

    public function test_acessar_listagem_apos_login_real_via_sessao_nao_recursiona(): void
    {
        // actingAs() injeta o usuário direto no guard, sem passar pela resolução
        // via sessão — por isso não pegaria a recursão infinita entre o escopo
        // global de empresa do Motorista e o TenantContext (guard 'motorista'
        // precisando consultar o próprio Motorista para descobrir a empresa).
        // Aqui simulamos uma requisição real: login via POST e, antes da
        // próxima requisição, forçamos o guard a esquecer o usuário em cache
        // (forgetGuards) para que ele seja resolvido de novo a partir da sessão.
        $motorista = Motorista::factory()->comAcessoPortal('minha-senha')->create(['cpf' => '123.456.789-10']);
        Viagem::factory()->create(['motorista_id' => $motorista->id]);

        $this->post(route('portal.login'), [
            'cpf'      => '123.456.789-10',
            'password' => 'minha-senha',
        ]);

        $this->app['auth']->forgetGuards();

        $response = $this->get(route('portal.viagens.index'));

        $response->assertOk();
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
