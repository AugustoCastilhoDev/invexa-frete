<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class ExportacaoDadosTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ve_a_tela_de_exportacao(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $response = $this->get(route('exportacao-dados.index'));

        $response->assertOk();
        $response->assertSee('Exportar Dados');
    }

    public function test_operador_nao_acessa_exportacao(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('exportacao-dados.index'));

        $response->assertForbidden();
    }

    public function test_baixar_gera_zip_com_dados_apenas_da_propria_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $adminA = User::factory()->admin()->create(['empresa_id' => $empresaA->id]);
        Cliente::factory()->create(['empresa_id' => $empresaA->id, 'nome' => 'Cliente da Empresa A']);

        $userB = User::factory()->create(['empresa_id' => $empresaB->id]);
        $this->actingAs($userB);
        Cliente::factory()->create(['empresa_id' => $empresaB->id, 'nome' => 'Cliente da Empresa B']);
        Viagem::factory()->create(['empresa_id' => $empresaB->id]);

        $this->actingAs($adminA);
        $response = $this->get(route('exportacao-dados.baixar'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/zip');

        $caminho = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive();
        $zip->open($caminho);

        $this->assertNotFalse($zip->locateName('clientes.csv'));
        $conteudoClientes = $zip->getFromName('clientes.csv');
        $this->assertStringContainsString('Cliente da Empresa A', $conteudoClientes);
        $this->assertStringNotContainsString('Cliente da Empresa B', $conteudoClientes);

        $zip->close();
    }
}
