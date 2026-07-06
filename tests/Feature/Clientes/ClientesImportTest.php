<?php

namespace Tests\Feature\Clientes;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ClientesImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_importa_clientes_validos_do_csv(): void
    {
        $this->actingAs(User::factory()->create());

        $csv = "tipo_pessoa;nome;cpf_cnpj;status\n"
            . "juridica;Transportes Exemplo;12.345.678/0001-90;ativo\n"
            . "fisica;Fulano de Tal;111.222.333-44;ativo\n";

        $arquivo = UploadedFile::fake()->createWithContent('clientes.csv', $csv);

        $response = $this->post(route('clientes.importar.store'), ['arquivo' => $arquivo]);

        $response->assertRedirect(route('clientes.index'));
        $this->assertDatabaseHas('clientes', ['cpf_cnpj' => '12.345.678/0001-90']);
        $this->assertDatabaseHas('clientes', ['cpf_cnpj' => '111.222.333-44']);
        $this->assertSame(2, session('importacao')['importados']);
    }

    public function test_documento_duplicado_e_reportado_como_erro(): void
    {
        $this->actingAs(User::factory()->create());
        Cliente::factory()->create(['cpf_cnpj' => '12.345.678/0001-90']);

        $csv = "tipo_pessoa;nome;cpf_cnpj;status\njuridica;Transportes Exemplo;12.345.678/0001-90;ativo\n";
        $arquivo = UploadedFile::fake()->createWithContent('clientes.csv', $csv);

        $this->post(route('clientes.importar.store'), ['arquivo' => $arquivo]);

        $resultado = session('importacao');
        $this->assertSame(0, $resultado['importados']);
        $this->assertCount(1, $resultado['erros']);
    }

    public function test_tipo_pessoa_invalido_e_reportado_como_erro(): void
    {
        $this->actingAs(User::factory()->create());

        $csv = "tipo_pessoa;nome;cpf_cnpj;status\ninvalido;Fulano;111.222.333-44;ativo\n";
        $arquivo = UploadedFile::fake()->createWithContent('clientes.csv', $csv);

        $this->post(route('clientes.importar.store'), ['arquivo' => $arquivo]);

        $resultado = session('importacao');
        $this->assertSame(0, $resultado['importados']);
        $this->assertCount(1, $resultado['erros']);
    }
}
