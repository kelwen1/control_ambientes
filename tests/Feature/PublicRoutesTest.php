<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Rutas públicas sin tocar base de datos ni migraciones.
 */
class PublicRoutesTest extends TestCase
{
    #[Test]
    public function welcome_responds_ok(): void
    {
        $response = $this->get('/');
        $response->assertOk();
    }

    #[Test]
    public function login_form_responds_ok(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
    }

    #[Test]
    public function manual_usuario_responds_ok(): void
    {
        $response = $this->get('/manual-usuario');
        $response->assertOk();
    }

    #[Test]
    public function health_check_responds_ok(): void
    {
        $response = $this->get('/up');
        $response->assertOk();
    }
}
