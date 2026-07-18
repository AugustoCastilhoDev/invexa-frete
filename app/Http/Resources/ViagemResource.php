<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViagemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'motorista' => $this->whenLoaded('motorista', fn () => [
                'id' => $this->motorista->id,
                'nome' => $this->motorista->nome,
            ]),
            'veiculo' => $this->whenLoaded('veiculo', fn () => [
                'id' => $this->veiculo->id,
                'placa' => $this->veiculo->placa,
            ]),
            'cliente' => $this->whenLoaded('cliente', fn () => $this->cliente ? [
                'id' => $this->cliente->id,
                'nome' => $this->cliente->nome,
            ] : null),
            'origem' => $this->origem,
            'origem_uf' => $this->origem_uf,
            'destino' => $this->destino,
            'destino_uf' => $this->destino_uf,
            'data_saida' => $this->data_saida?->toDateString(),
            'data_retorno' => $this->data_retorno?->toDateString(),
            'km_inicial' => $this->km_inicial,
            'km_final' => $this->km_final,
            'valor_frete' => $this->valor_frete,
            'valor_motorista' => $this->valor_motorista,
            'saldo_motorista' => $this->saldo_motorista,
            'lucro_transportadora' => $this->lucro_transportadora,
            'frete_recebido' => $this->frete_recebido,
            'observacoes' => $this->observacoes,
            'criado_em' => $this->created_at?->toIso8601String(),
            'atualizado_em' => $this->updated_at?->toIso8601String(),
        ];
    }
}
