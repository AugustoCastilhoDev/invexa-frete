<?php

namespace Tests\Feature\Veiculos;

use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VeiculosCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_veiculo_com_dados_validos(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('veiculos.store'), [
            'placa'  => 'ABC1D23',
            'modelo' => 'FH 540',
            'tipo'   => 'carreta',
            'status' => 'ativo',
        ]);

        $response->assertRedirect(route('veiculos.index'));
        $this->assertDatabaseHas('veiculos', ['placa' => 'ABC1D23']);
    }

    public function test_nao_permite_placa_duplicada(): void
    {
        $this->actingAs(User::factory()->create());
        Veiculo::factory()->create(['placa' => 'XYZ9K88']);

        $response = $this->post(route('veiculos.store'), [
            'placa'  => 'XYZ9K88',
            'modelo' => 'Actros',
            'tipo'   => 'truck',
            'status' => 'ativo',
        ]);

        $response->assertSessionHasErrors('placa');
    }

    public function test_busca_filtra_por_placa_modelo_ou_marca(): void
    {
        $this->actingAs(User::factory()->create());

        $encontrado = Veiculo::factory()->create(['modelo' => 'Constellation 24-280']);
        Veiculo::factory()->create(['modelo' => 'Delivery Express']);

        $response = $this->get(route('veiculos.index', ['busca' => 'Constellation']));

        $response->assertOk();
        $response->assertViewHas('veiculos', function ($veiculos) use ($encontrado) {
            return $veiculos->total() === 1 && $veiculos->first()->is($encontrado);
        });
    }

    public function test_exclusao_e_soft_delete(): void
    {
        $this->actingAs(User::factory()->create());
        $veiculo = Veiculo::factory()->create();

        $response = $this->delete(route('veiculos.destroy', $veiculo));

        $response->assertRedirect(route('veiculos.index'));
        $this->assertSoftDeleted($veiculo);
    }
}
