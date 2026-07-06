<?php

namespace Tests\Unit\Services\Asaas;

use App\Services\Asaas\AsaasClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AsaasClientTest extends TestCase
{
    public function test_nao_chama_a_api_quando_nao_ha_chave_configurada(): void
    {
        config(['services.asaas.api_key' => null]);
        Http::fake();

        $client = new AsaasClient();

        $this->assertNull($client->criarCliente(['nome' => 'Empresa', 'email' => 'a@a.com']));
        Http::assertNothingSent();
    }

    public function test_retorna_id_do_cliente_criado_com_sucesso(): void
    {
        config(['services.asaas.api_key' => 'chave-de-teste']);
        Http::fake(['*/customers' => Http::response(['id' => 'cus_abc'], 200)]);

        $client = new AsaasClient();
        $id = $client->criarCliente(['nome' => 'Empresa', 'email' => 'a@a.com']);

        $this->assertSame('cus_abc', $id);
    }

    public function test_retorna_null_quando_api_falha_ao_criar_cliente(): void
    {
        config(['services.asaas.api_key' => 'chave-de-teste']);
        Http::fake(['*/customers' => Http::response(['errors' => []], 400)]);

        $client = new AsaasClient();
        $id = $client->criarCliente(['nome' => 'Empresa', 'email' => 'a@a.com']);

        $this->assertNull($id);
    }

    public function test_retorna_id_da_assinatura_criada_com_sucesso(): void
    {
        config(['services.asaas.api_key' => 'chave-de-teste']);
        Http::fake(['*/subscriptions' => Http::response(['id' => 'sub_xyz'], 200)]);

        $client = new AsaasClient();
        $id = $client->criarAssinatura('cus_abc', [
            'valor' => 590.00,
            'ciclo' => 'MONTHLY',
            'proxima_cobranca' => now()->addDays(14)->format('Y-m-d'),
        ]);

        $this->assertSame('sub_xyz', $id);
    }

    public function test_usa_url_de_sandbox_por_padrao(): void
    {
        config(['services.asaas.env' => 'sandbox']);
        $this->assertSame('https://sandbox.asaas.com/api/v3', (new AsaasClient())->baseUrl());
    }

    public function test_usa_url_de_producao_quando_configurado(): void
    {
        config(['services.asaas.env' => 'production']);
        $this->assertSame('https://api.asaas.com/v3', (new AsaasClient())->baseUrl());
    }
}
