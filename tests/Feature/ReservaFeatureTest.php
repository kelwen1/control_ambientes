<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ambiente;
use App\Models\Ficha;
use App\Models\Programa;
use App\Models\Reserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReservaFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Programa $programa;
    protected Ambiente $ambiente;
    protected Ficha $ficha;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedEstadoReserva();
        $this->admin = User::factory()->create(['id_rol' => 1]);
        $this->programa = Programa::create(['nombre_programa' => 'Programa Test']);
        $this->ambiente = Ambiente::create([
            'num_ambiente' => '101',
            'id_estado' => 1,
            'capacidad_max' => 40,
        ]);
        $this->ficha = Ficha::create([
            'num_ficha' => '2557843',
            'cant_aprendices' => 25,
            'id_programa' => $this->programa->id_programa,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-06-30',
            'fecha_productiva' => null,
        ]);
    }

    protected function seedEstadoReserva(): void
    {
        DB::table('estado_reserva')->insert([
            ['id_estado_reserva' => 1, 'nombre_estado' => 'Activa'],
            ['id_estado_reserva' => 2, 'nombre_estado' => 'Cancelada'],
            ['id_estado_reserva' => 3, 'nombre_estado' => 'Finalizada'],
        ]);
    }

    /** @test */
    public function formulario_crear_reserva_requiere_autenticacion(): void
    {
        $this->get(route('reservas.create'))->assertRedirect(route('login'));
    }

    /** @test */
    public function formulario_crear_reserva_muestra_ambientes_y_fichas(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reservas.create'));

        $response->assertStatus(200);
        $response->assertViewIs('reservas.create');
        $response->assertSee('Asignar Ambiente a Ficha', false);
        $response->assertViewHas('ambientes');
        $response->assertViewHas('fichas');
        $response->assertViewHas('jornadas');
    }

    /** @test */
    public function puede_crear_reserva_con_jornada_manana(): void
    {
        $response = $this->actingAs($this->admin)->post(route('reservas.store'), [
            '_token' => csrf_token(),
            'id_ambiente' => $this->ambiente->id_ambiente,
            'id_ficha' => $this->ficha->id_ficha,
            'dia_semana' => 'lunes',
            'jornada' => 'manana',
            'hora_inicio' => '07:00',
            'hora_fin' => '13:00',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-02-28',
            'observaciones' => null,
        ]);

        $response->assertRedirect(route('ambientes.index'));
        $response->assertSessionHas('success', 'Reserva creada correctamente.');

        $this->assertDatabaseHas('reservas', [
            'id_ambiente' => $this->ambiente->id_ambiente,
            'id_ficha' => $this->ficha->id_ficha,
            'dia_semana' => 'lunes',
            'id_estado_reserva' => 1,
        ]);
        $reserva = \App\Models\Reserva::where('id_ambiente', $this->ambiente->id_ambiente)->where('dia_semana', 'lunes')->first();
        $this->assertNotNull($reserva);
        $this->assertTrue($reserva->hora_inicio <= '07:01' && $reserva->hora_inicio >= '06:59');
        $this->assertTrue($reserva->hora_fin <= '13:01' && $reserva->hora_fin >= '12:59');
    }

    /** @test */
    public function formulario_editar_reserva_muestra_datos_y_preselecciona_jornada(): void
    {
        // Sábados/domingos: una sola jornada 7:00-17:00
        $reserva = Reserva::create([
            'id_ambiente' => $this->ambiente->id_ambiente,
            'id_ficha' => $this->ficha->id_ficha,
            'dia_semana' => 'sabado',
            'hora_inicio' => '07:00',
            'hora_fin' => '17:00',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-02-28',
            'id_estado_reserva' => 1,
            'observaciones' => null,
        ]);

        $response = $this->actingAs($this->admin)->get(route('reservas.edit', $reserva->id_reserva));

        $response->assertStatus(200);
        $response->assertViewIs('reservas.edit');
        $response->assertViewHas('reserva');
        $response->assertViewHas('jornadas');
        $response->assertViewHas('jornadaSeleccionada', 'fin_semana');
    }

    /** @test */
    public function puede_actualizar_reserva(): void
    {
        $reserva = Reserva::create([
            'id_ambiente' => $this->ambiente->id_ambiente,
            'id_ficha' => $this->ficha->id_ficha,
            'dia_semana' => 'lunes',
            'hora_inicio' => '07:00',
            'hora_fin' => '13:00',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-02-28',
            'id_estado_reserva' => 1,
            'observaciones' => null,
        ]);

        $otroAmbiente = Ambiente::create([
            'num_ambiente' => '102',
            'id_estado' => 1,
            'capacidad_max' => 35,
        ]);

        $response = $this->actingAs($this->admin)->put(route('reservas.update', $reserva->id_reserva), [
            '_token' => csrf_token(),
            '_method' => 'PUT',
            'id_ambiente' => $otroAmbiente->id_ambiente,
            'id_ficha' => $this->ficha->id_ficha,
            'dia_semana' => 'lunes',
            'jornada' => 'tarde',
            'hora_inicio' => '13:00',
            'hora_fin' => '19:00',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-03-15',
            'id_estado_reserva' => 1,
            'observaciones' => 'Observación actualizada',
        ]);

        $response->assertRedirect(route('ambientes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reservas', [
            'id_reserva' => $reserva->id_reserva,
            'id_ambiente' => $otroAmbiente->id_ambiente,
            'observaciones' => 'Observación actualizada',
        ]);
        $reserva->refresh();
        $this->assertNotNull($reserva->hora_inicio);
        $this->assertNotNull($reserva->hora_fin);
        $this->assertStringContainsString('13', (string) $reserva->hora_inicio);
        $this->assertStringContainsString('19', (string) $reserva->hora_fin);
    }

    /** @test */
    public function crear_reserva_sin_jornada_valida_falla(): void
    {
        $response = $this->actingAs($this->admin)->post(route('reservas.store'), [
            '_token' => csrf_token(),
            'id_ambiente' => $this->ambiente->id_ambiente,
            'id_ficha' => $this->ficha->id_ficha,
            'dia_semana' => 'lunes',
            'hora_inicio' => '',
            'hora_fin' => '',
            'fecha_inicio' => '2026-02-01',
            'fecha_fin' => '2026-02-28',
        ]);

        $response->assertSessionHasErrors();
    }
}
