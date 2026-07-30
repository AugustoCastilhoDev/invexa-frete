<?php

namespace Database\Factories;

use App\Models\EmissaoFiscal;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartaCorrecaoCteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'emissao_fiscal_id' => EmissaoFiscal::factory()->state(['tipo' => 'cte']),
            'campo_corrigido' => 'observacoes',
            'valor_corrigido' => $this->faker->sentence(),
            'numero_carta_correcao' => 1,
            'status_sefaz' => '135',
            'mensagem_sefaz' => 'Evento registrado e vinculado a CT-e',
            'caminho_xml' => $this->faker->url(),
        ];
    }
}
