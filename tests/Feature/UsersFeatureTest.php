<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsersFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $usuarioNormal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['id_rol' => 1, 'user' => 'admin']);
        $this->usuarioNormal = User::factory()->create(['id_rol' => 3, 'user' => 'usuario']);
    }

    /** @test */
    public function listado_usuarios_solo_para_administrador(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));

        $this->actingAs($this->usuarioNormal)->get(route('users.index'))
            ->assertRedirect(route('dashboard'));

        $response = $this->actingAs($this->admin)->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertViewIs('users.index');
        $response->assertViewHas('users');
    }

    /** @test */
    public function formulario_crear_usuario_solo_para_admin(): void
    {
        $this->actingAs($this->usuarioNormal)->get(route('users.create'))
            ->assertRedirect(route('dashboard'));

        $response = $this->actingAs($this->admin)->get(route('users.create'));
        $response->assertStatus(200);
        $response->assertViewIs('users.create');
    }

    /** @test */
    public function admin_puede_crear_usuario(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            '_token' => csrf_token(),
            'id_cedula' => '9876543210',
            'nombre' => 'Nuevo',
            'apellido' => 'Usuario',
            'correo' => 'nuevo@test.com',
            'telefono' => '3001112233',
            'user' => 'nuevouser',
            'contraseña' => 'Pass123!@#',
            'contraseña_confirmation' => 'Pass123!@#',
            'id_rol' => 3,
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'Usuario creado correctamente.');

        $this->assertDatabaseHas('users', [
            'id_cedula' => '9876543210',
            'user' => 'nuevouser',
            'correo' => 'nuevo@test.com',
            'id_rol' => 3,
        ]);
    }

    /** @test */
    public function crear_usuario_con_contraseña_debil_falla(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            '_token' => csrf_token(),
            'id_cedula' => '9876543211',
            'nombre' => 'Otro',
            'apellido' => 'User',
            'correo' => 'otro@test.com',
            'user' => 'otrouser',
            'contraseña' => 'debil',
            'contraseña_confirmation' => 'debil',
            'id_rol' => 3,
        ]);

        $response->assertSessionHasErrors('contraseña');
    }

    /** @test */
    public function formulario_editar_usuario_solo_para_admin(): void
    {
        $this->actingAs($this->usuarioNormal)
            ->get(route('users.edit', $this->admin->id_cedula))
            ->assertRedirect(route('dashboard'));

        $response = $this->actingAs($this->admin)
            ->get(route('users.edit', $this->usuarioNormal->id_cedula));
        $response->assertStatus(200);
        $response->assertViewIs('users.edit');
        $response->assertViewHas('user');
    }

    /** @test */
    public function admin_puede_actualizar_usuario(): void
    {
        $response = $this->actingAs($this->admin)->put(route('users.update', $this->usuarioNormal->id_cedula), [
            '_token' => csrf_token(),
            '_method' => 'PUT',
            'id_cedula' => $this->usuarioNormal->id_cedula,
            'nombre' => 'Nombre',
            'apellido' => 'Actualizado',
            'correo' => $this->usuarioNormal->correo,
            'telefono' => '3009998877',
            'user' => $this->usuarioNormal->user,
            'id_rol' => 3,
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id_cedula' => $this->usuarioNormal->id_cedula,
            'nombre' => 'Nombre',
            'apellido' => 'Actualizado',
            'telefono' => '3009998877',
        ]);
    }

    /** @test */
    public function busqueda_usuarios_filtra_por_nombre_o_correo(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index', [
            'search' => $this->admin->nombre,
        ]));

        $response->assertStatus(200);
        $response->assertSee($this->admin->nombre, false);
    }
}
