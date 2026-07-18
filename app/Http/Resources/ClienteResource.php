<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo_pessoa' => $this->tipo_pessoa,
            'nome' => $this->nome,
            'razao_social' => $this->razao_social,
            'documento' => $this->documento_formatado,
            'ie' => $this->ie,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'celular' => $this->celular,
            'endereco' => [
                'cep' => $this->cep,
                'logradouro' => $this->logradouro,
                'numero' => $this->numero,
                'complemento' => $this->complemento,
                'bairro' => $this->bairro,
                'cidade' => $this->cidade,
                'estado' => $this->estado,
            ],
            'tabela_frete' => $this->tabela_frete,
            'status' => $this->status,
            'criado_em' => $this->created_at?->toIso8601String(),
            'atualizado_em' => $this->updated_at?->toIso8601String(),
        ];
    }
}
