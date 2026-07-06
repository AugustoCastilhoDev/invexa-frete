<?php

namespace App\Services\Asaas;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasClient
{
    public function baseUrl(): string
    {
        return config('services.asaas.env') === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://sandbox.asaas.com/api/v3';
    }

    public function configurado(): bool
    {
        return filled(config('services.asaas.api_key'));
    }

    /**
     * Cria o cliente (empresa) no Asaas. Retorna o id ("cus_...") ou null se
     * a chamada falhar — o cadastro da empresa no sistema não deve travar
     * por causa disso, só fica sem assinatura vinculada até ser resolvido.
     */
    public function criarCliente(array $dados): ?string
    {
        if (! $this->configurado()) {
            Log::warning('Asaas: tentativa de criar cliente sem ASAAS_API_KEY configurada.');

            return null;
        }

        $response = $this->http()->post('/customers', [
            'name' => $dados['nome'],
            'email' => $dados['email'],
            'cpfCnpj' => $dados['cpf_cnpj'] ?? null,
        ]);

        if ($response->failed()) {
            Log::warning('Asaas: falha ao criar cliente.', ['response' => $response->json()]);

            return null;
        }

        return $response->json('id');
    }

    /**
     * Cria a assinatura recorrente para o cliente. Retorna o id ("sub_...")
     * ou null se falhar.
     */
    public function criarAssinatura(string $customerId, array $dados): ?string
    {
        if (! $this->configurado()) {
            Log::warning('Asaas: tentativa de criar assinatura sem ASAAS_API_KEY configurada.');

            return null;
        }

        $response = $this->http()->post('/subscriptions', [
            'customer' => $customerId,
            'billingType' => 'UNDEFINED',
            'value' => $dados['valor'],
            'cycle' => $dados['ciclo'],
            'nextDueDate' => $dados['proxima_cobranca'],
            'description' => $dados['descricao'] ?? null,
        ]);

        if ($response->failed()) {
            Log::warning('Asaas: falha ao criar assinatura.', ['response' => $response->json()]);

            return null;
        }

        return $response->json('id');
    }

    private function http()
    {
        return Http::baseUrl($this->baseUrl())
            ->withHeaders(['access_token' => config('services.asaas.api_key')])
            ->acceptJson();
    }
}
