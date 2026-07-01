<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeploymentConfigurationTest extends TestCase
{
    public function test_aplicacao_usa_fuso_de_sao_paulo(): void
    {
        $this->assertSame('America/Sao_Paulo', config('app.timezone'));
    }

    public function test_perfil_hostinger_nao_depende_de_servicos_residentes(): void
    {
        $environment = file_get_contents(
            base_path('deploy/hostinger/env.production.example')
        );

        $this->assertStringContainsString('APP_ENV=production', $environment);
        $this->assertStringContainsString('APP_DEBUG=false', $environment);
        $this->assertStringContainsString('QUEUE_CONNECTION=sync', $environment);
        $this->assertStringContainsString('CACHE_STORE=file', $environment);
        $this->assertStringContainsString('SESSION_ENCRYPT=true', $environment);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $environment);
        $this->assertStringNotContainsString('QUEUE_CONNECTION=redis', $environment);
    }

    public function test_front_controller_da_hostinger_mantem_aplicacao_fora_do_public_html(): void
    {
        $frontController = file_get_contents(
            base_path('deploy/hostinger/public-index.php')
        );

        $this->assertStringContainsString(
            "dirname(__DIR__).'/laravel_app'",
            $frontController
        );
        $this->assertStringNotContainsString("require __DIR__.'/../vendor", $frontController);
    }

    public function test_endpoint_de_saude_esta_disponivel(): void
    {
        $this->get('/up')->assertOk();
    }
}
