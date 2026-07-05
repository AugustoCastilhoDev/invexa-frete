<?php

namespace Tests\Feature\Motoristas;

use App\Models\Motorista;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotoristasCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_motorista_com_dados_validos(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('motoristas.store'), [
            'nome'                => 'João da Silva',
            'cpf'                 => '12345678901',
            'percentual_comissao' => 12.5,
            'status'              => 'ativo',
        ]);

        $response->assertRedirect(route('motoristas.index'));
        $this->assertDatabaseHas('motoristas', ['cpf' => '12345678901']);
    }

    public function test_nao_permite_cpf_duplicado(): void
    {
        $this->actingAs(User::factory()->create());
        Motorista::factory()->create(['cpf' => '11122233344']);

        $response = $this->post(route('motoristas.store'), [
            'nome'                => 'Outro Motorista',
            'cpf'                 => '11122233344',
            'percentual_comissao' => 10,
            'status'              => 'ativo',
        ]);

        $response->assertSessionHasErrors('cpf');
    }

    public function test_busca_filtra_por_nome_cpf_ou_telefone(): void
    {
        $this->actingAs(User::factory()->create());

        $encontrado = Motorista::factory()->create(['nome' => 'Carlos Andrade']);
        Motorista::factory()->create(['nome' => 'Maria Souza']);

        $response = $this->get(route('motoristas.index', ['busca' => 'Andrade']));

        $response->assertOk();
        $response->assertViewHas('motoristas', function ($motoristas) use ($encontrado) {
            return $motoristas->total() === 1 && $motoristas->first()->is($encontrado);
        });
    }

    public function test_exclusao_e_soft_delete(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $motorista = Motorista::factory()->create();

        $response = $this->delete(route('motoristas.destroy', $motorista));

        $response->assertRedirect(route('motoristas.index'));
        $this->assertSoftDeleted($motorista);
    }

    public function test_operador_nao_pode_excluir_motorista(): void
    {
        $this->actingAs(User::factory()->create());
        $motorista = Motorista::factory()->create();

        $response = $this->delete(route('motoristas.destroy', $motorista));

        $response->assertForbidden();
        $this->assertNotSoftDeleted($motorista);
    }
}
