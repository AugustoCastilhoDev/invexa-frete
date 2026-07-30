<?php

namespace Tests\Unit\Models;

use App\Models\Empresa;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_pagamento_em_atraso_apenas_quando_status_e_payment_overdue(): void
    {
        $empresa = Empresa::factory()->create(['asaas_status' => 'PAYMENT_OVERDUE']);
        $this->assertTrue($empresa->pagamentoEmAtraso());

        $empresa->asaas_status = 'PAYMENT_RECEIVED';
        $this->assertFalse($empresa->pagamentoEmAtraso());
    }

    #[DataProvider('situacoesCobranca')]
    public function test_situacao_cobranca_traduz_o_status_bruto_do_asaas(?string $statusBruto, string $labelEsperado): void
    {
        $empresa = Empresa::factory()->create(['asaas_status' => $statusBruto]);

        $this->assertSame($labelEsperado, $empresa->situacaoCobranca()['label']);
    }

    public static function situacoesCobranca(): array
    {
        return [
            'sem assinatura' => [null, 'Sem assinatura'],
            'em trial' => ['em_trial', 'Em trial'],
            'pagamento recebido' => ['PAYMENT_RECEIVED', 'Em dia'],
            'pagamento confirmado' => ['PAYMENT_CONFIRMED', 'Em dia'],
            'pagamento em atraso' => ['PAYMENT_OVERDUE', 'Atrasado'],
            'pagamento reembolsado' => ['PAYMENT_REFUNDED', 'Reembolsado'],
            'status desconhecido' => ['ALGO_INESPERADO', 'Pendente'],
        ];
    }

    public function test_situacao_certificado_inativo_quando_focus_nfe_desligado(): void
    {
        $empresa = Empresa::factory()->create([
            'focus_nfe_ativo' => false,
            'focus_nfe_certificado_validade' => now()->addDays(5),
        ]);

        $this->assertSame('Inativo', $empresa->situacaoCertificado()['label']);
    }

    public function test_situacao_certificado_sem_validade_registrada(): void
    {
        $empresa = Empresa::factory()->create([
            'focus_nfe_ativo' => true,
            'focus_nfe_certificado_validade' => null,
        ]);

        $this->assertSame('Sem validade registrada', $empresa->situacaoCertificado()['label']);
    }

    public function test_situacao_certificado_vencido(): void
    {
        $empresa = Empresa::factory()->create([
            'focus_nfe_ativo' => true,
            'focus_nfe_certificado_validade' => now()->subDay(),
        ]);

        $this->assertSame('Vencido', $empresa->situacaoCertificado()['label']);
    }

    public function test_situacao_certificado_vence_em_breve_dentro_de_30_dias(): void
    {
        $empresa = Empresa::factory()->create([
            'focus_nfe_ativo' => true,
            'focus_nfe_certificado_validade' => now()->addDays(10),
        ]);

        $this->assertSame('Vence em breve', $empresa->situacaoCertificado()['label']);
    }

    public function test_situacao_certificado_valido_fora_da_janela_de_alerta(): void
    {
        $empresa = Empresa::factory()->create([
            'focus_nfe_ativo' => true,
            'focus_nfe_certificado_validade' => now()->addDays(90),
        ]);

        $this->assertSame('Válido', $empresa->situacaoCertificado()['label']);
    }
}
