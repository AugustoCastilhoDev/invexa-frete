<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_termos_de_uso_esta_acessivel_sem_login(): void
    {
        $response = $this->get(route('legal.termos'));

        $response->assertOk();
        $response->assertSee('Termos de Uso');
    }

    public function test_politica_de_privacidade_esta_acessivel_sem_login(): void
    {
        $response = $this->get(route('legal.privacidade'));

        $response->assertOk();
        $response->assertSee('Política de Privacidade');
    }

    public function test_landing_page_linka_termos_e_privacidade(): void
    {
        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee(route('legal.termos'), false);
        $response->assertSee(route('legal.privacidade'), false);
    }
}
