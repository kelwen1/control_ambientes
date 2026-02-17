<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_muestra_formulario(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('user', false);
        $response->assertSee('contraseña', false);
    }

    #[Test]
    public function login_con_credenciales_correctas_redirige_al_dashboard(): void
    {
        $user = User::factory()->create([
            'user' => 'testuser',
            'contraseña' => Hash::make('password123'),
            'id_rol' => 1,
        ]);

        $response = $this->post(route('login'), [
            'user' => 'testuser',
            'contraseña' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function login_con_credenciales_incorrectas_vuelve_con_error(): void
    {
        User::factory()->create([
            'user' => 'testuser',
            'contraseña' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'user' => 'testuser',
            'contraseña' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('user');
        $this->assertGuest();
    }

    #[Test]
    public function login_con_usuario_inexistente_no_revela_informacion(): void
    {
        $response = $this->post(route('login'), [
            'user' => 'noexiste',
            'contraseña' => 'cualquiercosa',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('user');
        $this->assertGuest();
    }

    #[Test]
    public function logout_cierra_sesion_y_redirige(): void
    {
        $user = User::factory()->create(['id_rol' => 1]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** @test */
    public function rutas_protegidas_redirigen_a_login_sin_autenticacion(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('fichas.index'))->assertRedirect(route('login'));
        $this->get(route('ambientes.index'))->assertRedirect(route('login'));
        $this->get(route('reservas.create'))->assertRedirect(route('login'));
        $this->get(route('ambientes.disponibilidad'))->assertRedirect(route('login'));
    }

    #[Test]
    public function usuario_autenticado_accede_al_dashboard(): void
    {
        $user = User::factory()->create(['id_rol' => 1]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }
}
