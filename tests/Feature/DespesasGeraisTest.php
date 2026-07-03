<?php

namespace Tests\Feature;

use App\Models\DespesaGeral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DespesasGeraisTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $response = $this->get(route('despesas-gerais.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_cadastra_despesa_geral(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('despesas-gerais.store'), [
            'categoria'    => 'aluguel',
            'descricao'    => 'Aluguel do pátio',
            'valor'        => 2500,
            'data_despesa' => now()->format('Y-m-d'),
            'recorrente'   => '1',
        ]);

        $response->assertRedirect(route('despesas-gerais.index'));
        $this->assertDatabaseHas('despesas_gerais', [
            'categoria'  => 'aluguel',
            'descricao'  => 'Aluguel do pátio',
            'recorrente' => 1,
        ]);
    }

    public function test_index_totaliza_apenas_despesas_do_periodo_e_categoria_selecionados(): void
    {
        $this->actingAs(User::factory()->create());

        $dataDentro = Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d');

        DespesaGeral::factory()->create([
            'categoria'    => 'aluguel',
            'valor'        => 1000,
            'data_despesa' => $dataDentro,
        ]);

        DespesaGeral::factory()->create([
            'categoria'    => 'salarios',
            'valor'        => 5000,
            'data_despesa' => $dataDentro,
        ]);

        // fora do período
        DespesaGeral::factory()->create([
            'data_despesa' => Carbon::now()->subMonths(2)->format('Y-m-d'),
        ]);

        $response = $this->get(route('despesas-gerais.index', ['categoria' => 'aluguel']));

        $response->assertOk();
        $response->assertViewHas('total', 1000.0);
    }

    public function test_atualiza_despesa_geral(): void
    {
        $this->actingAs(User::factory()->create());

        $despesa = DespesaGeral::factory()->create(['valor' => 100]);

        $response = $this->put(route('despesas-gerais.update', $despesa), [
            'categoria'    => $despesa->categoria,
            'descricao'    => 'Atualizada',
            'valor'        => 300,
            'data_despesa' => $despesa->data_despesa->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('despesas-gerais.index'));
        $this->assertEquals(300, $despesa->fresh()->valor);
        $this->assertEquals('Atualizada', $despesa->fresh()->descricao);
    }

    public function test_remove_despesa_geral(): void
    {
        $this->actingAs(User::factory()->create());

        $despesa = DespesaGeral::factory()->create();

        $response = $this->delete(route('despesas-gerais.destroy', $despesa));

        $response->assertRedirect(route('despesas-gerais.index'));
        $this->assertDatabaseMissing('despesas_gerais', ['id' => $despesa->id]);
    }
}
