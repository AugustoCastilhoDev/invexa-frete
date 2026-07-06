<?php

namespace Tests\Feature\Motoristas;

use App\Models\Motorista;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MotoristasImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_importa_motoristas_validos_do_csv(): void
    {
        $this->actingAs(User::factory()->create());

        $csv = "nome;cpf;percentual_comissao;status\n"
            . "João da Silva;111.111.111-11;10;ativo\n"
            . "Maria Souza;222.222.222-22;12;ativo\n";

        $arquivo = UploadedFile::fake()->createWithContent('motoristas.csv', $csv);

        $response = $this->post(route('motoristas.importar.store'), ['arquivo' => $arquivo]);

        $response->assertRedirect(route('motoristas.index'));
        $this->assertDatabaseHas('motoristas', ['cpf' => '111.111.111-11', 'nome' => 'João da Silva']);
        $this->assertDatabaseHas('motoristas', ['cpf' => '222.222.222-22']);
        $this->assertSame(2, session('importacao')['importados']);
    }

    public function test_linha_com_cpf_duplicado_e_reportada_como_erro(): void
    {
        $this->actingAs(User::factory()->create());
        Motorista::factory()->create(['cpf' => '111.111.111-11']);

        $csv = "nome;cpf;percentual_comissao;status\n"
            . "João da Silva;111.111.111-11;10;ativo\n";

        $arquivo = UploadedFile::fake()->createWithContent('motoristas.csv', $csv);

        $this->post(route('motoristas.importar.store'), ['arquivo' => $arquivo]);

        $resultado = session('importacao');
        $this->assertSame(0, $resultado['importados']);
        $this->assertCount(1, $resultado['erros']);
    }

    public function test_tela_de_importacao_carrega(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('motoristas.importar'));

        $response->assertOk();
    }
}
