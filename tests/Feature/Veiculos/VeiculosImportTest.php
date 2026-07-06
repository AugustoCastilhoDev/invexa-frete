<?php

namespace Tests\Feature\Veiculos;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class VeiculosImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_importa_veiculos_validos_do_csv(): void
    {
        $this->actingAs(User::factory()->create());

        $csv = "placa;modelo;tipo;status\n"
            . "ABC1D23;FH 540;truck;ativo\n"
            . "XYZ9K88;Actros;carreta;ativo\n";

        $arquivo = UploadedFile::fake()->createWithContent('veiculos.csv', $csv);

        $response = $this->post(route('veiculos.importar.store'), ['arquivo' => $arquivo]);

        $response->assertRedirect(route('veiculos.index'));
        $this->assertDatabaseHas('veiculos', ['placa' => 'ABC1D23']);
        $this->assertDatabaseHas('veiculos', ['placa' => 'XYZ9K88']);
        $this->assertSame(2, session('importacao')['importados']);
    }

    public function test_respeita_limite_de_veiculos_do_plano_durante_a_importacao(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => 1]);
        $admin = User::factory()->admin()->create(['empresa_id' => $empresa->id]);
        Veiculo::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin);

        $csv = "placa;modelo;tipo;status\nABC1D23;FH 540;truck;ativo\n";
        $arquivo = UploadedFile::fake()->createWithContent('veiculos.csv', $csv);

        $this->post(route('veiculos.importar.store'), ['arquivo' => $arquivo]);

        $resultado = session('importacao');
        $this->assertSame(0, $resultado['importados']);
        $this->assertCount(1, $resultado['erros']);
        $this->assertDatabaseMissing('veiculos', ['placa' => 'ABC1D23']);
    }

    public function test_placa_duplicada_e_reportada_como_erro_sem_interromper_as_demais(): void
    {
        $this->actingAs(User::factory()->create());
        Veiculo::factory()->create(['placa' => 'ABC1D23']);

        $csv = "placa;modelo;tipo;status\n"
            . "ABC1D23;FH 540;truck;ativo\n"
            . "XYZ9K88;Actros;truck;ativo\n";

        $arquivo = UploadedFile::fake()->createWithContent('veiculos.csv', $csv);

        $this->post(route('veiculos.importar.store'), ['arquivo' => $arquivo]);

        $resultado = session('importacao');
        $this->assertSame(1, $resultado['importados']);
        $this->assertCount(1, $resultado['erros']);
        $this->assertDatabaseHas('veiculos', ['placa' => 'XYZ9K88']);
    }
}
