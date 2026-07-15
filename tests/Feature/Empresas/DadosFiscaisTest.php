<?php

namespace Tests\Feature\Empresas;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DadosFiscaisTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_atualiza_dados_fiscais_da_empresa(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $empresa = Empresa::factory()->create();

        $response = $this->patch(route('empresas.dados-fiscais.atualizar', $empresa), [
            'cep' => '80010-000',
            'logradouro' => 'Rua das Flores',
            'numero' => '100',
            'bairro' => 'Centro',
            'municipio' => 'Curitiba',
            'codigo_municipio' => '4106902',
            'uf' => 'PR',
            'telefone' => '(41) 3333-4444',
            'inscricao_estadual' => '1234567890',
            'rntrc' => '12345678',
            'regime_tributario' => 'simples_nacional',
            'cfop_padrao' => '6353',
            'icms_situacao_tributaria' => '40',
            'icms_aliquota' => 12.5,
        ]);

        $response->assertRedirect(route('empresas.show', $empresa));
        $empresa->refresh();
        $this->assertSame('Curitiba', $empresa->municipio);
        $this->assertSame('4106902', $empresa->codigo_municipio);
        $this->assertSame('6353', $empresa->cfop_padrao);
        $this->assertEquals(12.5, $empresa->icms_aliquota);
    }

    public function test_admin_comum_nao_pode_atualizar_dados_fiscais(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $empresa = Empresa::factory()->create();

        $response = $this->patch(route('empresas.dados-fiscais.atualizar', $empresa), [
            'cfop_padrao' => '6353',
        ]);

        $response->assertForbidden();
    }
}
