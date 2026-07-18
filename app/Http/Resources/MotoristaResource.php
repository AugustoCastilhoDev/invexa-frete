<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MotoristaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'cnh' => $this->cnh,
            'categoria_cnh' => $this->categoria_cnh,
            'validade_cnh' => $this->validade_cnh?->toDateString(),
            'telefone' => $this->telefone,
            'email' => $this->email,
            'percentual_comissao' => $this->percentual_comissao,
            'status' => $this->status,
            'criado_em' => $this->created_at?->toIso8601String(),
            'atualizado_em' => $this->updated_at?->toIso8601String(),
        ];
    }
}
