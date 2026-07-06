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

    public function test_carreta_vinculada_a_cavalo_conta_como_1_conjunto(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => 1]);
        $cavalo  = Veiculo::factory()->create(['tipo' => 'truck', 'empresa_id' => $empresa->id]);
        Veiculo::factory()->vinculadaA($cavalo)->create(['empresa_id' => $empresa->id]);

        // Cavalo + carreta vinculada = 1 conjunto, então o limite de 1 já está atingido
        // (não 2, como seria se a carreta contasse separadamente).
        $this->assertTrue($empresa->limiteVeiculosAtingido());
    }

    public function test_carreta_avulsa_sem_cavalo_conta_separadamente(): void
    {
        $empresa = Empresa::factory()->create(['limite_veiculos' => 2]);
        $cavalo  = Veiculo::factory()->create(['tipo' => 'truck', 'empresa_id' => $empresa->id]);
        Veiculo::factory()->carreta()->create(['empresa_id' => $empresa->id]);

        // Cavalo (1) + carreta avulsa sem vínculo (1) = 2, atinge o limite de 2.
        $this->assertTrue($empresa->limiteVeiculosAtingido());
    }
}
