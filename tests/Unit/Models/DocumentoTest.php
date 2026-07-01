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
}
