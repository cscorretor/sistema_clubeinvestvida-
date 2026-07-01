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

    public function test_deploy_forca_php_82_tambem_para_o_composer(): void
    {
        $script = file_get_contents(
            base_path('deploy/hostinger/deploy.sh')
        );

        $this->assertStringContainsString('/opt/alt/php82/usr/bin/php', $script);
        $this->assertStringContainsString(
            '"$PHP_BIN" "$COMPOSER_PATH" install',
            $script
        );
        $this->assertStringContainsString(
            'PHP 8.2 é obrigatório',
            $script
        );
    }

    public function test_configurador_interativo_protege_segredos_e_gera_app_key(): void
    {
        $script = file_get_contents(
            base_path('deploy/hostinger/configure-env.sh')
        );

        $this->assertStringContainsString('read -rsp', $script);
        $this->assertStringContainsString('random_bytes(32)', $script);
        $this->assertStringContainsString('env.production.example', $script);
        $this->assertStringContainsString("'DB_HOST' => '127.0.0.1'", $script);
        $this->assertStringContainsString('chmod 600', $script);
        $this->assertStringNotContainsString('echo "$DB_PASSWORD"', $script);
        $this->assertStringNotContainsString('echo "$ADMIN_PASSWORD"', $script);
    }

    public function test_endpoint_de_saude_esta_disponivel(): void
    {
        $this->get('/up')->assertOk();
    }
}
