<?php

namespace Tests\Unit\Models;

use App\Models\Documento;
use App\Models\Motorista;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendenciasScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_cnh_vencendo_ignora_motorista_inativo(): void
    {
        Motorista::factory()->inativo()->create(['validade_cnh' => now()->addDays(5)]);

        $this->assertEquals(0, Motorista::cnhVencendo()->count());
    }

    public function test_scope_cnh_vencendo_ignora_cnh_fora_do_prazo(): void
    {
        Motorista::factory()->create(['status' => 'ativo', 'validade_cnh' => now()->addDays(60)]);

        $this->assertEquals(0, Motorista::cnhVencendo(30)->count());
    }

    public function test_scope_cnh_vencendo_inclui_cnh_ja_vencida(): void
    {
        Motorista::factory()->create(['status' => 'ativo', 'validade_cnh' => now()->subDays(5)]);

        $this->assertEquals(1, Motorista::cnhVencendo()->count());
    }

    public function test_scope_em_manutencao_filtra_por_status(): void
    {
        Veiculo::factory()->emManutencao()->create();
        Veiculo::factory()->create();

        $this->assertEquals(1, Veiculo::emManutencao()->count());
    }

    public function test_scope_pendentes_filtra_documentos_por_status(): void
    {
        Documento::factory()->create(['status' => 'pendente']);
        Documento::factory()->create(['status' => 'autorizado']);
        Documento::factory()->create(['status' => 'cancelado']);

        $this->assertEquals(1, Documento::pendentes()->count());
    }
}
