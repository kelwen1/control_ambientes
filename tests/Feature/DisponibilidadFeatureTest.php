<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ambiente;
use App\Models\Ficha;
use App\Models\Programa;
use App\Models\Reserva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DisponibilidadFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Ambiente $ambienteLibre;
    protected Ambiente $ambienteOcupadoSabadoTarde;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedEstadoReserva();
        $this->user = User::factory()->create(['id_rol' => 1]);

        $this->ambienteLibre = Ambiente::create([
            'num_ambiente' => '28',
            'id_estado' => 1,
            'capacidad_max' => 40,
        ]);

        $this->ambienteOcupadoSabadoTarde = Ambiente::create([
            'num_ambiente' => '30',
            'id_estado' => 3,
            'capacidad_max' => 40,
        ]);

        $programa = Programa::create(['nombre_programa' => 'Test']);
        $ficha = Ficha::create([
            'num_ficha' => '3063365',
            'cant_aprendices' => 30,
            'id_programa' => $programa->id_programa,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-06-30',
            'fecha_productiva' => null,
        ]);

        // Sábados y domingos: una sola reserva por ambiente (7:00-17:00)
        Reserva::create([
            'id_ambiente' => $this->ambienteOcupadoSabadoTarde->id_ambiente,
            'id_ficha' => $ficha->id_ficha,
            'dia_semana' => 'sabado',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '17:00:00',
            'fecha_inicio' => '2026-01-07',
            'fecha_fin' => '2026-02-06',
            'id_estado_reserva' => 1,
            'observaciones' => null,
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
    public function disponibilidad_requiere_autenticacion(): void
    {
        $this->get(route('ambientes.disponibilidad'))->assertRedirect(route('login'));
    }

    /** @test */
    public function disponibilidad_sin_filtros_muestra_vista_sin_resultados(): void
    {
        $response = $this->actingAs($this->user)->get(route('ambientes.disponibilidad'));

        $response->assertStatus(200);
        $response->assertViewIs('ambientes.disponibilidad');
        $response->assertSee('Disponibilidad por Jornada', false);
        $response->assertSee('Ver disponibilidad', false);
    }

    /** @test */
    public function disponibilidad_lunes_viernes_manana_incluye_ambientes_sin_reserva_ese_dia(): void
    {
        $response = $this->actingAs($this->user)->get(route('ambientes.disponibilidad', [
            'dia_tipo' => 'lunes_viernes',
            'jornada' => 'manana',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('ambientes');
        $ambientes = $response->viewData('ambientes');
        $nums = $ambientes->pluck('num_ambiente')->toArray();
        $this->assertContains('28', $nums);
        $this->assertContains('30', $nums);
    }

    /** @test */
    public function disponibilidad_sabado_fin_semana_excluye_ambiente_con_reserva_activa(): void
    {
        $response = $this->actingAs($this->user)->get(route('ambientes.disponibilidad', [
            'dia_tipo' => 'sabado',
            'jornada' => 'fin_semana',
        ]));

        $response->assertStatus(200);
        $ambientes = $response->viewData('ambientes');
        $nums = $ambientes->pluck('num_ambiente')->toArray();
        $this->assertNotContains('30', $nums);
        $this->assertContains('28', $nums);
    }

    /** @test */
    public function disponibilidad_domingo_fin_semana_incluye_ambiente_sin_reserva_domingo(): void
    {
        $response = $this->actingAs($this->user)->get(route('ambientes.disponibilidad', [
            'dia_tipo' => 'domingo',
            'jornada' => 'fin_semana',
        ]));

        $response->assertStatus(200);
        $ambientes = $response->viewData('ambientes');
        $nums = $ambientes->pluck('num_ambiente')->map(fn ($n) => (string) $n)->toArray();
        $this->assertContains('28', $nums);
        $this->assertContains('30', $nums, 'El ambiente 30 no tiene reserva el domingo, debe estar disponible.');
    }

    /** @test */
    public function lista_disponibles_muestra_estado_disponible(): void
    {
        $response = $this->actingAs($this->user)->get(route('ambientes.disponibilidad', [
            'dia_tipo' => 'lunes_viernes',
            'jornada' => 'noche',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Disponible', false);
    }
}
