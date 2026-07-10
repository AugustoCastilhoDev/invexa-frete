<?php

namespace Tests\Feature\Empresas;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FocusNfeActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_ativa_focus_nfe_para_uma_empresa(): void
    {
        Storage::fake(config('filesystems.uploads_disk'));
        config(['services.focus_nfe.token_conta_principal' => 'token-conta-principal']);
        Http::fake([
            '*/v2/empresas' => Http::response(['id' => 'foc_123', 'token' => 'token-empresa-abc', 'status' => 'ativo'], 200),
        ]);

        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create(['cnpj' => '11.222.333/0001-44']);

        $response = $this->post(route('empresas.focus-nfe.ativar', $empresa), [
            'ambiente' => 'homologacao',
            'certificado' => UploadedFile::fake()->create('certificado.pfx', 10),
            'certificado_senha' => 'senha-do-certificado',
        ]);

        $response->assertRedirect(route('empresas.show', $empresa));
        $empresa->refresh();
        $this->assertTrue($empresa->focus_nfe_ativo);
        $this->assertSame('homologacao', $empresa->focus_nfe_ambiente);
        $this->assertSame('foc_123', $empresa->focus_nfe_empresa_id);
        $this->assertSame('token-empresa-abc', $empresa->focus_nfe_token);
        $this->assertSame('senha-do-certificado', $empresa->focus_nfe_certificado_senha);
        $this->assertNotNull($empresa->focus_nfe_certificado_path);

        // As colunas são 'encrypted' — o valor bruto no banco não pode ser o texto puro.
        $bruto = \DB::table('empresas')->where('id', $empresa->id)->first();
        $this->assertNotSame('token-empresa-abc', $bruto->focus_nfe_token);
        $this->assertNotSame('senha-do-certificado', $bruto->focus_nfe_certificado_senha);
    }

    public function test_admin_comum_nao_pode_ativar_focus_nfe(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $empresa = Empresa::factory()->create();

        $response = $this->post(route('empresas.focus-nfe.ativar', $empresa), [
            'ambiente' => 'homologacao',
            'certificado' => UploadedFile::fake()->create('certificado.pfx', 10),
            'certificado_senha' => 'senha',
        ]);

        $response->assertForbidden();
    }

    public function test_falha_no_registro_nao_ativa_e_remove_certificado_salvo(): void
    {
        Storage::fake(config('filesystems.uploads_disk'));
        config(['services.focus_nfe.token_conta_principal' => 'token-conta-principal']);
        Http::fake(['*/v2/empresas' => Http::response(['erro' => 'cnpj invalido'], 422)]);

        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create();

        $response = $this->post(route('empresas.focus-nfe.ativar', $empresa), [
            'ambiente' => 'homologacao',
            'certificado' => UploadedFile::fake()->create('certificado.pfx', 10),
            'certificado_senha' => 'senha',
        ]);

        $response->assertRedirect();
        $this->assertFalse($empresa->fresh()->focus_nfe_ativo);
        Storage::disk(config('filesystems.uploads_disk'))->assertDirectoryEmpty('certificados');
    }

    public function test_super_admin_desativa_focus_nfe_sem_apagar_token(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->focusNfeAtivo()->create();

        $response = $this->patch(route('empresas.focus-nfe.desativar', $empresa));

        $response->assertRedirect(route('empresas.show', $empresa));
        $empresa->refresh();
        $this->assertFalse($empresa->focus_nfe_ativo);
        $this->assertSame('token-teste', $empresa->focus_nfe_token);
    }
}
