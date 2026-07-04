<?php

namespace Tests\Unit\Models;

use App\Models\Empresa;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaTest extends TestCase
{
    use RefreshDatabase;

    public function test_sem_limite_definido_nunca_atinge_o_limite(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => null]);
        Veiculo::factory()->count(5)->create(['empresa_id' => $empresa->id]);

        $this->assertFalse($empresa->limiteVeiculosAtingido());
    }

    public function test_abaixo_do_limite_nao_atingiu(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => 3]);
        Veiculo::factory()->count(2)->create(['empresa_id' => $empresa->id]);

        $this->assertFalse($empresa->limiteVeiculosAtingido());
    }

    public function test_no_limite_exato_ja_atingiu(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => 3]);
        Veiculo::factory()->count(3)->create(['empresa_id' => $empresa->id]);

        $this->assertTrue($empresa->limiteVeiculosAtingido());
    }
}
