<?php

namespace Tests;

use App\Models\Empresa;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    /**
     * A maioria dos testes cria dados (motorista, veículo, viagem...) sem se
     * preocupar com multi-tenant. Forçamos aqui uma empresa de teste como
     * fallback: assim que o teste autenticar um usuário/motorista real, o
     * contexto passa a vir dele, e tudo criado antes ou depois converge para
     * a mesma empresa — sem precisar tocar nos 200+ testes existentes.
     *
     * Só roda se o teste já migrou o banco (RefreshDatabase); alguns testes
     * não tocam banco nenhum.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('empresas')) {
            TenantContext::forceId(Empresa::factory()->create()->id);
        }
    }
}
