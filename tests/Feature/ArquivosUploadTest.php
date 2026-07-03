<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArquivosUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_comprovante_de_lancamento_e_salvo_no_disco_de_uploads_configurado(): void
    {
        Storage::fake('public');
        $viagem = Viagem::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('lancamentos.store', $viagem), [
            'tipo'            => 'combustivel',
            'descricao'       => 'Abastecimento',
            'valor'           => 200,
            'data_lancamento' => now()->format('Y-m-d'),
            'comprovante'     => UploadedFile::fake()->create('recibo.jpg', 50),
        ]);

        $lancamento = $viagem->lancamentos()->first();
        Storage::disk('public')->assertExists($lancamento->comprovante);
        $this->assertStringContainsString('/storage/', $lancamento->comprovante_url);
    }

    public function test_arquivo_de_documento_e_salvo_no_disco_de_uploads_configurado(): void
    {
        Storage::fake('public');
        $viagem = Viagem::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('documentos.store', $viagem), [
            'tipo'         => 'nfe',
            'numero'       => '12345',
            'data_emissao' => now()->format('Y-m-d'),
            'valor'        => 500,
            'status'       => 'autorizado',
            'arquivo'      => UploadedFile::fake()->create('nota.pdf', 100),
        ]);

        $documento = $viagem->documentos()->first();
        Storage::disk('public')->assertExists($documento->arquivo);
        $this->assertStringContainsString('/storage/', $documento->arquivo_url);
    }

    public function test_url_do_arquivo_e_assinada_quando_disco_de_uploads_e_s3(): void
    {
        Config::set('filesystems.uploads_disk', 'r2');
        Config::set('filesystems.disks.r2.driver', 's3');
        Storage::fake('r2');

        $viagem = Viagem::factory()->create();
        $documento = $viagem->documentos()->create([
            'tipo'         => 'nfe',
            'numero'       => '1',
            'data_emissao' => now(),
            'valor'        => 10,
            'status'       => 'autorizado',
            'arquivo'      => 'documentos/nota.pdf',
        ]);

        $this->assertNotNull($documento->arquivo_url);
    }

    public function test_sem_arquivo_a_url_e_nula(): void
    {
        $viagem = Viagem::factory()->create();
        $documento = $viagem->documentos()->create([
            'tipo'         => 'nfe',
            'numero'       => '1',
            'data_emissao' => now(),
            'valor'        => 10,
            'status'       => 'autorizado',
            'arquivo'      => null,
        ]);

        $this->assertNull($documento->arquivo_url);
    }
}
