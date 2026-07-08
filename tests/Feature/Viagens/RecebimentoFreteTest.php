<?php

namespace Tests\Feature\Viagens;

use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecebimentoFreteTest extends TestCase
{
    use RefreshDatabase;

    public function test_viagem_nasce_com_frete_nao_recebido(): void
    {
        $viagem = Viagem::factory()->create()->fresh();

        $this->assertFalse($viagem->frete_recebido);
        $this->assertNull($viagem->data_recebimento_frete);
    }

    public function test_marcar_recebimento_confirma_e_registra_data(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create(['frete_recebido' => false]);

        $response = $this->patch(route('viagens.recebimento', $viagem));

        $response->assertRedirect();
        $viagem->refresh();
        $this->assertTrue($viagem->frete_recebido);
        $this->assertEquals(now()->format('Y-m-d'), $viagem->data_recebimento_frete->format('Y-m-d'));
    }

    public function test_marcar_recebimento_novamente_desfaz_a_confirmacao(): void
    {
        $this->actingAs(User::factory()->create());

        $viagem = Viagem::factory()->create([
            'frete_recebido'         => true,
            'data_recebimento_frete' => now(),
        ]);

        $response = $this->patch(route('viagens.recebimento', $viagem));

        $response->assertRedirect();
        $viagem->refresh();
        $this->assertFalse($viagem->frete_recebido);
        $this->assertNull($viagem->data_recebimento_frete);
    }

    public function test_filtra_viagens_por_recebimento(): void
    {
        $this->actingAs(User::factory()->create());

        Viagem::factory()->create(['frete_recebido' => true, 'origem' => 'Recebida']);
        Viagem::factory()->create(['frete_recebido' => false, 'origem' => 'PendenteOrigem']);

        $response = $this->get(route('viagens.index', ['recebimento' => 'recebido']));

        $response->assertOk();
        $response->assertSee('Recebida');
        $response->assertDontSee('PendenteOrigem');
    }

    public function test_exporta_csv_de_viagens_com_coluna_de_recebimento(): void
    {
        $this->actingAs(User::factory()->create());

        Viagem::factory()->create([
            'origem'          => 'São Paulo',
            'destino'         => 'Rio de Janeiro',
            'frete_recebido'  => true,
            'data_recebimento_frete' => now(),
        ]);

        $response = $this->get(route('viagens.csv'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $conteudo = $response->streamedContent();
        $this->assertStringContainsString('Frete Recebido', $conteudo);
        $this->assertStringContainsString('Sim', $conteudo);
    }
}
