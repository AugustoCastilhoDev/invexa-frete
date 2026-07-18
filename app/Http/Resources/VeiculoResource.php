<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VeiculoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'placa' => $this->placa,
            'modelo' => $this->modelo,
            'marca' => $this->marca,
            'ano' => $this->ano,
            'tipo' => $this->tipo,
            'renavam' => $this->renavam,
            'chassi' => $this->chassi,
            'validade_documento' => $this->validade_documento?->toDateString(),
            'capacidade_kg' => $this->capacidade_kg,
            'tara_kg' => $this->tara_kg,
            'cavalo_id' => $this->cavalo_id,
            'status' => $this->status,
            'criado_em' => $this->created_at?->toIso8601String(),
            'atualizado_em' => $this->updated_at?->toIso8601String(),
        ];
    }
}
