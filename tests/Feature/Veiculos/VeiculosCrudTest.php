<?php

namespace Tests\Feature\Veiculos;

use App\Models\Empresa;
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

    public function test_cria_veiculo_com_chassi_e_validade_documento(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('veiculos.store'), [
            'placa'              => 'ABC1D23',
            'modelo'             => 'FH 540',
            'tipo'               => 'carreta',
            'status'             => 'ativo',
            'chassi'             => '9BWZZZ377VT004251',
            'validade_documento' => '2027-03-15',
        ]);

        $response->assertRedirect(route('veiculos.index'));
        $this->assertDatabaseHas('veiculos', [
            'placa'  => 'ABC1D23',
            'chassi' => '9BWZZZ377VT004251',
        ]);

        $veiculo = Veiculo::where('placa', 'ABC1D23')->first();
        $this->assertSame('2027-03-15', $veiculo->validade_documento->format('Y-m-d'));
    }

    public function test_atualiza_chassi_e_validade_documento_do_veiculo(): void
    {
        $this->actingAs(User::factory()->create());
        $veiculo = Veiculo::factory()->create();

        $response = $this->put(route('veiculos.update', $veiculo), [
            'placa'              => $veiculo->placa,
            'modelo'             => $veiculo->modelo,
            'tipo'               => $veiculo->tipo,
            'status'             => $veiculo->status,
            'chassi'             => '9BWZZZ377VT004251',
            'validade_documento' => '2027-03-15',
        ]);

        $response->assertRedirect(route('veiculos.index'));
        $veiculo->refresh();
        $this->assertSame('9BWZZZ377VT004251', $veiculo->chassi);
        $this->assertSame('2027-03-15', $veiculo->validade_documento->format('Y-m-d'));
    }

    public function test_vincula_carreta_a_um_cavalo_ao_cadastrar(): void
    {
        $this->actingAs(User::factory()->create());
        $cavalo = Veiculo::factory()->create(['tipo' => 'truck']);

        $response = $this->post(route('veiculos.store'), [
            'placa'     => 'CAR1R23',
            'modelo'    => 'Carreta Graneleira',
            'tipo'      => 'carreta',
            'status'    => 'ativo',
            'cavalo_id' => $cavalo->id,
        ]);

        $response->assertRedirect(route('veiculos.index'));
        $this->assertDatabaseHas('veiculos', ['placa' => 'CAR1R23', 'cavalo_id' => $cavalo->id]);
    }

    public function test_atualiza_vinculo_de_carreta_a_cavalo(): void
    {
        $this->actingAs(User::factory()->create());
        $cavalo  = Veiculo::factory()->create(['tipo' => 'truck']);
        $carreta = Veiculo::factory()->carreta()->create();

        $response = $this->put(route('veiculos.update', $carreta), [
            'placa'     => $carreta->placa,
            'modelo'    => $carreta->modelo,
            'tipo'      => 'carreta',
            'status'    => 'ativo',
            'cavalo_id' => $cavalo->id,
        ]);

        $response->assertRedirect(route('veiculos.index'));
        $this->assertSame($cavalo->id, $carreta->fresh()->cavalo_id);
    }

    public function test_nao_permite_cavalo_id_em_veiculo_que_nao_e_carreta(): void
    {
        $this->actingAs(User::factory()->create());
        $cavalo = Veiculo::factory()->create(['tipo' => 'truck']);

        $response = $this->post(route('veiculos.store'), [
            'placa'     => 'UTL1L23',
            'modelo'    => 'Van de Carga',
            'tipo'      => 'utilitario',
            'status'    => 'ativo',
            'cavalo_id' => $cavalo->id,
        ]);

        $response->assertSessionHasErrors('cavalo_id');
        $this->assertDatabaseMissing('veiculos', ['placa' => 'UTL1L23']);
    }

    public function test_nao_permite_vincular_carreta_a_veiculo_que_nao_e_cavalo(): void
    {
        $this->actingAs(User::factory()->create());
        $outraCarreta = Veiculo::factory()->carreta()->create();

        $response = $this->post(route('veiculos.store'), [
            'placa'     => 'CAR2R23',
            'modelo'    => 'Carreta Sider',
            'tipo'      => 'carreta',
            'status'    => 'ativo',
            'cavalo_id' => $outraCarreta->id,
        ]);

        $response->assertSessionHasErrors('cavalo_id');
        $this->assertDatabaseMissing('veiculos', ['placa' => 'CAR2R23']);
    }

    public function test_carreta_vinculada_a_um_cavalo_nao_conta_separadamente_no_limite_do_plano(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => 1]);
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id]);
        $cavalo  = Veiculo::factory()->create(['tipo' => 'truck', 'empresa_id' => $empresa->id]);
        Veiculo::factory()->vinculadaA($cavalo)->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin);

        // Limite de 1 já preenchido pelo cavalo; a carreta vinculada não deveria contar,
        // então cadastrar um segundo veículo avulso ainda deve ser bloqueado (limite = 1).
        $response = $this->get(route('veiculos.index'));

        $response->assertOk();
        $response->assertSee('1 / 1');
    }

    public function test_carreta_avulsa_sem_cavalo_conta_no_limite_do_plano(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => 1]);
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id]);
        Veiculo::factory()->carreta()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin);

        $response = $this->post(route('veiculos.store'), [
            'placa'  => 'ABC1D23',
            'modelo' => 'FH 540',
            'tipo'   => 'truck',
            'status' => 'ativo',
        ]);

        $response->assertSessionHasErrors('placa');
        $this->assertDatabaseMissing('veiculos', ['placa' => 'ABC1D23']);
    }

    public function test_tela_de_detalhe_exibe_cavalo_vinculado_da_carreta(): void
    {
        $this->actingAs(User::factory()->create());
        $cavalo  = Veiculo::factory()->create(['tipo' => 'truck', 'placa' => 'CAV1L23']);
        $carreta = Veiculo::factory()->vinculadaA($cavalo)->create();

        $response = $this->get(route('veiculos.show', $carreta));

        $response->assertOk();
        $response->assertSee('CAV1L23');
    }

    public function test_tela_de_detalhe_exibe_carretas_vinculadas_do_cavalo(): void
    {
        $this->actingAs(User::factory()->create());
        $cavalo = Veiculo::factory()->create(['tipo' => 'truck']);
        Veiculo::factory()->vinculadaA($cavalo)->create(['placa' => 'CAR3R23']);

        $response = $this->get(route('veiculos.show', $cavalo));

        $response->assertOk();
        $response->assertSee('CAR3R23');
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

    public function test_listagem_mostra_validade_do_documento(): void
    {
        $this->actingAs(User::factory()->create());
        Veiculo::factory()->create(['validade_documento' => now()->addDays(90)]);

        $response = $this->get(route('veiculos.index'));

        $response->assertOk();
        $response->assertSee(now()->addDays(90)->format('d/m/Y'));
    }

    public function test_listagem_destaca_em_vermelho_documento_vencendo_em_ate_30_dias(): void
    {
        $this->actingAs(User::factory()->create());
        Veiculo::factory()->create(['validade_documento' => now()->addDays(15)]);
        Veiculo::factory()->create(['validade_documento' => now()->addDays(90)]);

        $response = $this->get(route('veiculos.index'));

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'bi-exclamation-triangle-fill'));
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
        $this->actingAs(User::factory()->admin()->create());
        $veiculo = Veiculo::factory()->create();

        $response = $this->delete(route('veiculos.destroy', $veiculo));

        $response->assertRedirect(route('veiculos.index'));
        $this->assertSoftDeleted($veiculo);
    }

    public function test_operador_nao_pode_excluir_veiculo(): void
    {
        $this->actingAs(User::factory()->create());
        $veiculo = Veiculo::factory()->create();

        $response = $this->delete(route('veiculos.destroy', $veiculo));

        $response->assertForbidden();
        $this->assertNotSoftDeleted($veiculo);
    }

    public function test_bloqueia_cadastro_ao_atingir_limite_de_veiculos_do_plano(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => 1]);
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id]);
        Veiculo::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin);

        $response = $this->post(route('veiculos.store'), [
            'placa'  => 'ABC1D23',
            'modelo' => 'FH 540',
            'tipo'   => 'carreta',
            'status' => 'ativo',
        ]);

        $response->assertSessionHasErrors('placa');
        $this->assertDatabaseMissing('veiculos', ['placa' => 'ABC1D23']);
    }

    public function test_permite_cadastro_dentro_do_limite_de_veiculos_do_plano(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => 2]);
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id]);
        Veiculo::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin);

        $response = $this->post(route('veiculos.store'), [
            'placa'  => 'ABC1D23',
            'modelo' => 'FH 540',
            'tipo'   => 'carreta',
            'status' => 'ativo',
        ]);

        $response->assertRedirect(route('veiculos.index'));
        $this->assertDatabaseHas('veiculos', ['placa' => 'ABC1D23']);
    }

    public function test_sem_limite_definido_cadastro_e_ilimitado(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => null]);
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id]);
        Veiculo::factory()->count(10)->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin);

        $response = $this->post(route('veiculos.store'), [
            'placa'  => 'ABC1D23',
            'modelo' => 'FH 540',
            'tipo'   => 'carreta',
            'status' => 'ativo',
        ]);

        $response->assertRedirect(route('veiculos.index'));
        $this->assertDatabaseHas('veiculos', ['placa' => 'ABC1D23']);
    }

    public function test_tela_de_veiculos_mostra_quantidade_do_plano(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => 5]);
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id]);
        Veiculo::factory()->count(2)->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin);

        $response = $this->get(route('veiculos.index'));

        $response->assertOk();
        $response->assertSee('2 / 5');
        $response->assertDontSee('Você atingiu o limite');
    }

    public function test_tela_de_veiculos_avisa_quando_limite_e_atingido(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => 2]);
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id]);
        Veiculo::factory()->count(2)->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin);

        $response = $this->get(route('veiculos.index'));

        $response->assertOk();
        $response->assertSee('Você atingiu o limite');
    }

    public function test_tela_de_veiculos_nao_mostra_nada_quando_sem_limite(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => null]);
        $admin   = User::factory()->admin()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin);

        $response = $this->get(route('veiculos.index'));

        $response->assertOk();
        $response->assertDontSee('do seu plano');
    }
}
