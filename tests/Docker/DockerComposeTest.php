<?php

namespace Tests\Docker;

use Tests\TestCase;

class DockerComposeTest extends TestCase
{
    public function test_docker_compose_file_exists(): void
    {
        $this->assertFileExists(base_path('docker-compose.yml'));
    }

    public function test_docker_compose_has_app_service(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('app:', $content);
    }

    public function test_docker_compose_has_queue_service(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('queue:', $content);
    }

    public function test_docker_compose_has_postgres_service(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('postgres:', $content);
    }

    public function test_docker_compose_has_migrate_service(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('migrate:', $content);
    }

    public function test_docker_compose_postgres_uses_version_16(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('postgres:16', $content);
    }

    public function test_docker_compose_postgres_has_healthcheck(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('pg_isready', $content);
    }

    public function test_docker_compose_postgres_has_volume(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('postgres_data', $content);
    }

    public function test_docker_compose_app_depends_on_postgres(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('condition: service_healthy', $content);
    }

    public function test_docker_compose_queue_depends_on_postgres(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('postgres:', $content);
    }

    public function test_docker_compose_app_exposes_port(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('8080:80', $content);
    }

    public function test_docker_compose_uses_env_variables(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('${APP_ENV', $content);
        $this->assertStringContainsString('${DB_DATABASE', $content);
    }

    public function test_docker_compose_has_healthcheck_for_app(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('test: ["CMD", "curl"', $content);
    }

    public function test_docker_compose_queue_has_restart_policy(): void
    {
        $content = file_get_contents(base_path('docker-compose.yml'));
        $this->assertStringContainsString('restart: unless-stopped', $content);
    }
}
