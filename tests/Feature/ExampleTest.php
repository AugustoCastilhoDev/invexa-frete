<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_raiz_redireciona_para_dashboard_que_exige_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('dashboard'));

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }
}
