<?php

namespace Tests\Feature\Viagens;

use App\Models\Carga;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Empresa;
use App\Models\EmissaoFiscal;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CargasTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_carga_vinculada_a_um_cliente(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();
        $cliente = Cliente::factory()->create();

        $response = $this->post(route('cargas.store', $viagem), [
            'cliente_id' => $cliente->id,
            'valor_frete' => 850.50,
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertDatabaseHas('cargas', [
            'viagem_id' => $viagem->id,
            'cliente_id' => $cliente->id,
            'valor_frete' => 850.50,
        ]);
    }

    public function test_cria_carga_sem_valor_frete(): void
    {
        $this->actingAs(User::factory()->create());
        $viagem = Viagem::factory()->create();
        $cliente = Cliente::factory()->create();

        $response = $this->post(route('cargas.store', $viagem), [
            'cliente_id' => $cliente->id,
        ]);

        $response->assertRedirect(route('viagens.show', $viagem));
        $this->assertDatabaseHas('cargas', [
            'viagem_id' => $viagem->id,
            'cliente_id' => $cliente->id,
            'valor_frete' => null,
        ]);
    }

    public function test_nao_permite_criar_carga_em_viagem_de_outra_empresa(): void
    {
        // Usuário/cliente da empresa "padrão" precisam ser criados antes de
        // trocar o actingAs — o argumento de actingAs(User::factory()->create())
        // é avaliado com o auth ainda antigo, então criar depois herdaria o
        // tenant errado (mesmo bug que motivou o padrão de passar empresa_id
        // explícito nos outros testes multi-tenant deste arquivo/projeto).
        $usuarioPadrao = User::factory()->create();
        $cliente = Cliente::factory()->create();

        $outraEmpresa = Empresa::factory()->create();
        $usuarioDeOutraEmpresa = User::factory()->create(['empresa_id' => $outraEmpresa->id]);
        $this->actingAs($usuarioDeOutraEmpresa);
        $viagemDeOutraEmpresa = Viagem::factory()->create();

        $this->actingAs($usuarioPadrao);

        $response = $this->post(route('cargas.store', $viagemDeOutraEmpresa), [
            'cliente_id' => $cliente->id,
        ]);

        $response->assertNotFound();
    }

    public function test_remove_carga_sem_documentos_ou_emissoes(): void
    {
        $this->actingAs(User::factory()->create());
        $carga = Carga::factory()->create();

        $response = $this->delete(route('cargas.destroy', $carga));

        $response->assertRedirect(route('viagens.show', $carga->viagem));
        $this->assertDatabaseMissing('cargas', ['id' => $carga->id]);
    }

    public function test_nao_remove_carga_com_documento_vinculado(): void
    {
        $this->actingAs(User::factory()->create());
        $carga = Carga::factory()->create();
        Documento::factory()->create(['viagem_id' => $carga->viagem_id, 'carga_id' => $carga->id]);

        $response = $this->delete(route('cargas.destroy', $carga));

        $response->assertStatus(422);
        $this->assertDatabaseHas('cargas', ['id' => $carga->id]);
    }

    public function test_nao_remove_carga_com_emissao_fiscal_vinculada(): void
    {
        $this->actingAs(User::factory()->create());
        $carga = Carga::factory()->create();
        EmissaoFiscal::factory()->paraCarga($carga)->create();

        $response = $this->delete(route('cargas.destroy', $carga));

        $response->assertStatus(422);
        $this->assertDatabaseHas('cargas', ['id' => $carga->id]);
    }
}
