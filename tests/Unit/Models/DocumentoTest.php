<?php

namespace Tests\Unit\Models;

use App\Models\Documento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DocumentoTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('tiposFormatados')]
    public function test_tipo_formatado($tipo, $esperado): void
    {
        $documento = Documento::factory()->create(['tipo' => $tipo]);

        $this->assertEquals($esperado, $documento->tipo_formatado);
    }

    public static function tiposFormatados(): array
    {
        return [
            'cte'    => ['cte', 'CT-e'],
            'mdfe'   => ['mdfe', 'MDF-e'],
            'nfe'    => ['nfe', 'NF-e'],
            'outros' => ['outros', 'Outros'],
        ];
    }

    #[DataProvider('statusBadges')]
    public function test_status_badge($status, $esperado): void
    {
        $documento = Documento::factory()->create(['status' => $status]);

        $this->assertEquals($esperado, $documento->status_badge);
    }

    public static function statusBadges(): array
    {
        return [
            'autorizado' => ['autorizado', 'success'],
            'cancelado'  => ['cancelado', 'danger'],
            'pendente'   => ['pendente', 'warning'],
        ];
    }

    #[DataProvider('urlsConsultaSefaz')]
    public function test_url_consulta_sefaz($tipo, $chaveAcesso, $urlEsperada): void
    {
        $documento = Documento::factory()->create(['tipo' => $tipo, 'chave_acesso' => $chaveAcesso]);

        $this->assertEquals($urlEsperada, $documento->url_consulta_sefaz);
    }

    public static function urlsConsultaSefaz(): array
    {
        $chave = '35250000000000000000550010000000011000000017';

        return [
            'nfe com chave'      => ['nfe', $chave, 'https://www.nfe.fazenda.gov.br/portal/consultaRecaptcha.aspx'],
            'cte com chave'      => ['cte', $chave, 'https://www.cte.fazenda.gov.br/portal/consultaRecaptcha.aspx'],
            'mdfe com chave'     => ['mdfe', $chave, 'https://dfe-portal.svrs.rs.gov.br/Mdfe/consulta'],
            'outros com chave'   => ['outros', $chave, null],
            'nfe sem chave'      => ['nfe', null, null],
        ];
    }

    public function test_exige_login_gov_br_apenas_para_mdfe(): void
    {
        $mdfe = Documento::factory()->create(['tipo' => 'mdfe']);
        $cte  = Documento::factory()->create(['tipo' => 'cte']);

        $this->assertTrue($mdfe->exige_login_gov_br);
        $this->assertFalse($cte->exige_login_gov_br);
    }
}
