<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ficha;
use App\Models\Programa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FichasFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Programa $programa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['id_rol' => 1]);
        $this->programa = Programa::create(['nombre_programa' => 'ADSI']);
    }

    /** @test */
    public function listado_fichas_requiere_autenticacion(): void
    {
        $this->get(route('fichas.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function listado_fichas_muestra_vista_y_puede_estar_vacio(): void
    {
        $response = $this->actingAs($this->admin)->get(route('fichas.index'));

        $response->assertStatus(200);
        $response->assertViewIs('fichas.index');
        $response->assertViewHas('fichas');
    }

    /** @test */
    public function formulario_crear_ficha_muestra_programas(): void
    {
        $response = $this->actingAs($this->admin)->get(route('fichas.create'));

        $response->assertStatus(200);
        $response->assertViewIs('fichas.create');
        $response->assertViewHas('programas');
        $response->assertSee('ADSI', false);
    }

    /** @test */
    public function puede_crear_ficha(): void
    {
        $response = $this->actingAs($this->admin)->post(route('fichas.store'), [
            '_token' => csrf_token(),
            'num_ficha' => '2557843',
            'cant_aprendices' => 35,
            'id_programa' => $this->programa->id_programa,
            'fecha_inicio' => '2026-01-15',
            'fecha_fin' => '2026-07-15',
            'fecha_productiva' => null,
        ]);

        $response->assertRedirect(route('fichas.index'));
        $response->assertSessionHas('success', 'Ficha creada correctamente.');

        $this->assertDatabaseHas('ficha', [
            'num_ficha' => '2557843',
            'cant_aprendices' => 35,
            'id_programa' => $this->programa->id_programa,
        ]);
    }

    /** @test */
    public function crear_ficha_con_num_ficha_invalido_falla(): void
    {
        $response = $this->actingAs($this->admin)->post(route('fichas.store'), [
            '_token' => csrf_token(),
            'num_ficha' => '1234567890123',
            'cant_aprendices' => 35,
            'id_programa' => $this->programa->id_programa,
            'fecha_inicio' => '2026-01-15',
            'fecha_fin' => '2026-07-15',
        ]);

        $response->assertSessionHasErrors('num_ficha');
    }

    /** @test */
    public function formulario_editar_ficha_muestra_datos(): void
    {
        $ficha = Ficha::create([
            'num_ficha' => '2557843',
            'cant_aprendices' => 30,
            'id_programa' => $this->programa->id_programa,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-06-30',
            'fecha_productiva' => null,
        ]);

        $response = $this->actingAs($this->admin)->get(route('fichas.edit', $ficha->id_ficha));

        $response->assertStatus(200);
        $response->assertViewIs('fichas.edit');
        $response->assertViewHas('ficha', $ficha);
        $response->assertSee('2557843', false);
    }

    /** @test */
    public function puede_actualizar_ficha(): void
    {
        $ficha = Ficha::create([
            'num_ficha' => '2557843',
            'cant_aprendices' => 30,
            'id_programa' => $this->programa->id_programa,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-06-30',
            'fecha_productiva' => null,
        ]);

        $response = $this->actingAs($this->admin)->put(route('fichas.update', $ficha->id_ficha), [
            '_token' => csrf_token(),
            '_method' => 'PUT',
            'num_ficha' => '2557844',
            'cant_aprendices' => 32,
            'id_programa' => $this->programa->id_programa,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-07-31',
            'fecha_productiva' => null,
        ]);

        $response->assertRedirect(route('fichas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ficha', [
            'id_ficha' => $ficha->id_ficha,
            'num_ficha' => '2557844',
            'cant_aprendices' => 32,
        ]);
    }

    /** @test */
    public function busqueda_fichas_filtra_por_criterio(): void
    {
        Ficha::create([
            'num_ficha' => '111',
            'cant_aprendices' => 20,
            'id_programa' => $this->programa->id_programa,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-06-30',
            'fecha_productiva' => null,
        ]);

        $response = $this->actingAs($this->admin)->get(route('fichas.index', ['search' => '111']));

        $response->assertStatus(200);
        $response->assertSee('111', false);
    }
}
