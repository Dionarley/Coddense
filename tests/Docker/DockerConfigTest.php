<?php

namespace Tests\Docker;

use Tests\TestCase;

class DockerConfigTest extends TestCase
{
    public function test_dockerfile_exists(): void
    {
        $this->assertFileExists(base_path('Dockerfile'));
    }

    public function test_dockerfile_uses_php83(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('php:8.3', $content);
    }

    public function test_dockerfile_uses_alpine(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('alpine', $content);
    }

    public function test_dockerfile_installs_postgresql_dev(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('postgresql-dev', $content);
    }

    public function test_dockerfile_installs_sqlite_dev(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('sqlite-dev', $content);
    }

    public function test_dockerfile_installs_pdo_pgsql(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('pdo_pgsql', $content);
    }

    public function test_dockerfile_installs_pdo_sqlite(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('pdo_sqlite', $content);
    }

    public function test_dockerfile_installs_nginx(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('nginx', $content);
    }

    public function test_dockerfile_installs_supervisor(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('supervisor', $content);
    }

    public function test_dockerfile_exposes_port_80(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('EXPOSE 80', $content);
    }

    public function test_dockerfile_uses_supervisord_as_entrypoint(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('supervisord', $content);
    }

    public function test_dockerfile_copies_nginx_config(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('docker/nginx.conf', $content);
    }

    public function test_dockerfile_copies_supervisord_config(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('docker/supervisord.conf', $content);
    }

    public function test_dockerfile_runs_composer_install(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('composer install', $content);
    }

    public function test_dockerfile_runs_npm_build(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('npm run build', $content);
    }

    public function test_dockerfile_sets_correct_permissions(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('chown', $content);
        $this->assertStringContainsString('chmod', $content);
    }

    public function test_dockerfile_has_health_check_endpoint(): void
    {
        $content = file_get_contents(base_path('Dockerfile'));
        $this->assertStringContainsString('health.php', $content);
        $this->assertStringContainsString('http_response_code(200)', $content);
    }
}
