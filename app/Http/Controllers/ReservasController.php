<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Ambiente;
use App\Models\Ficha;
use App\Models\Persona;
use App\Models\SecurityAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreReservaRequest;
use App\Http\Requests\UpdateReservaRequest;

class ReservasController extends Controller
{
    /**
     * Actualiza el estado del ambiente basado en sus reservas activas.
     * Si hay al menos una reserva activa (no finalizada ni cancelada), marca el ambiente como Ocupado.
     * Si todas las reservas están finalizadas o canceladas, marca el ambiente como Disponible.
     */
    private function actualizarEstadoAmbiente($idAmbiente)
    {
        // Verificar si hay reservas activas (estado 1 = Activa) para este ambiente
        $tieneReservasActivas = DB::table('reservas')
            ->where('id_ambiente', $idAmbiente)
            ->where('id_estado_reserva', 1) // 1 = Activa
            ->exists();

        if ($tieneReservasActivas) {
            // Si hay reservas activas, marcar el ambiente como Ocupado (3)
            DB::table('ambientes')
                ->where('id_ambiente', $idAmbiente)
                ->update(['id_estado' => 3]); // 3 = Ocupado
        } else {
            // Si no hay reservas activas, verificar si hay alguna reserva
            $tieneReservas = DB::table('reservas')
                ->where('id_ambiente', $idAmbiente)
                ->exists();

            if ($tieneReservas) {
                // Hay reservas pero todas están finalizadas o canceladas
                // Marcar el ambiente como Disponible (1)
                DB::table('ambientes')
                    ->where('id_ambiente', $idAmbiente)
                    ->update(['id_estado' => 1]); // 1 = Disponible
            } else {
                // No hay reservas, marcar como Disponible (1)
                DB::table('ambientes')
                    ->where('id_ambiente', $idAmbiente)
                    ->update(['id_estado' => 1]); // 1 = Disponible
            }
        }
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create()
    {
        // Obtener todos los ambientes para el select
        $ambientes = DB::table('ambientes')
            ->select('id_ambiente', 'num_ambiente')
            ->orderByRaw('CAST(num_ambiente AS UNSIGNED), num_ambiente')
            ->get();

        // Obtener todas las fichas para el select
        $fichas = DB::table('ficha')
            ->select('id_ficha', 'num_ficha')
            ->orderBy('num_ficha', 'asc')
            ->get();

        $jornadas = config('jornadas');

        $instructores = Persona::where('id_rol', config('roles.ids.instructor', 4))
            ->whereHas('user')
            ->orderBy('nombres')
            ->get(['id_persona', 'nombres', 'apellidos']);

        return view('reservas.create', [
            'ambientes' => $ambientes,
            'fichas' => $fichas,
            'jornadas' => $jornadas,
            'instructores' => $instructores,
        ]);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(StoreReservaRequest $request)
    {
        // La validación se realiza automáticamente por el Form Request

        // Validar capacidad máxima del ambiente
        $ambiente = DB::table('ambientes')
            ->where('id_ambiente', $request->id_ambiente)
            ->first();
        
        if ($ambiente) {
            // Obtener capacidad máxima del ambiente (por defecto 35 si no está definida)
            $capacidadMaxima = $ambiente->capacidad_max ?? 35;
            
            // Obtener cantidad de aprendices de la ficha
            $ficha = DB::table('ficha')
                ->where('id_ficha', $request->id_ficha)
                ->first();
            
            if ($ficha && $ficha->cant_aprendices > $capacidadMaxima) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', "La cantidad de aprendices ({$ficha->cant_aprendices}) excede la capacidad máxima del ambiente ({$capacidadMaxima} aprendices).");
            }
        }

        // Validar que no haya solapamiento de horarios con reservas activas
        // Excluir reservas finalizadas (3) y canceladas (2) para que puedan reasignarse
        $conflictos = DB::table('reservas')
            ->where('id_ambiente', $request->id_ambiente)
            ->where('dia_semana', $request->dia_semana)
            ->where('id_estado_reserva', 1) // Solo verificar reservas activas (1)
            ->where(function($query) use ($request) {
                // Verificar solapamiento: dos intervalos se solapan si 
                // inicio1 < fin2 AND fin1 > inicio2
                $query->where(function($q) use ($request) {
                    $q->where('hora_inicio', '<', $request->hora_fin)
                      ->where('hora_fin', '>', $request->hora_inicio);
                });
            })
            ->exists();

        if ($conflictos) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ya existe una reserva activa en este ambiente para el día y horario seleccionado. Por favor, elige otro horario o día.');
        }

        // Crear la reserva
        $reserva = Reserva::create([
            'id_ambiente' => $request->id_ambiente,
            'id_ficha' => $request->id_ficha,
            'id_persona' => $request->id_persona ?: null,
            'dia_semana' => $request->dia_semana,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'id_estado_reserva' => 1, // Por defecto "Activa"
            'observaciones' => $request->observaciones ?? null,
        ]);

        // Registrar auditoría de creación de reserva
        try {
            $user = Auth::user();
            SecurityAuditLog::create([
                'user_id'       => $user?->id_cedula,
                'action'        => 'reserva_created',
                'resource_type' => 'reserva',
                'resource_id'   => $reserva->id_reserva ?? null,
                'description'   => 'Creación de reserva para ambiente ID ' . $request->id_ambiente . ' y ficha ID ' . $request->id_ficha,
                'ip_address'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // No romper el flujo si falla el log
        }

        // Actualizar el estado del ambiente basado en sus reservas
        $this->actualizarEstadoAmbiente($request->id_ambiente);

        return redirect()->route('ambientes.index')->with('success', 'Reserva creada correctamente.');
    }

    /**
     * Show the form for editing the specified reservation.
     */
    public function edit($id)
    {
        $reserva = DB::table('reservas')
            ->leftJoin('ambientes', 'reservas.id_ambiente', '=', 'ambientes.id_ambiente')
            ->leftJoin('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->leftJoin('estado_reserva', 'reservas.id_estado_reserva', '=', 'estado_reserva.id_estado_reserva')
            ->where('reservas.id_reserva', $id)
            ->select(
                'reservas.id_reserva',
                'reservas.id_ambiente',
                'reservas.id_ficha',
                'reservas.id_persona',
                'reservas.dia_semana',
                'reservas.hora_inicio',
                'reservas.hora_fin',
                'reservas.fecha_inicio',
                'reservas.fecha_fin',
                'reservas.id_estado_reserva',
                'reservas.observaciones',
                'ambientes.num_ambiente',
                'ficha.num_ficha',
                'estado_reserva.nombre_estado'
            )
            ->first();

        if (!$reserva) {
            return redirect()->route('ambientes.index')->with('error', 'Reserva no encontrada.');
        }

        // Obtener todos los ambientes para el select
        $ambientes = DB::table('ambientes')
            ->select('id_ambiente', 'num_ambiente')
            ->orderByRaw('CAST(num_ambiente AS UNSIGNED), num_ambiente')
            ->get();

        // Obtener todas las fichas para el select
        $fichas = DB::table('ficha')
            ->select('id_ficha', 'num_ficha')
            ->orderBy('num_ficha', 'asc')
            ->get();

        // Obtener todos los estados de reserva
        $estados = DB::table('estado_reserva')
            ->select('id_estado_reserva', 'nombre_estado')
            ->orderBy('id_estado_reserva')
            ->get();

        $jornadas = config('jornadas');
        $instructores = Persona::where('id_rol', config('roles.ids.instructor', 4))
            ->whereHas('user')
            ->orderBy('nombres')
            ->get(['id_persona', 'nombres', 'apellidos']);
        $horaInicio = $reserva->hora_inicio ? \Carbon\Carbon::parse($reserva->hora_inicio)->format('H:i') : null;
        $diaSemana = $reserva->dia_semana ?? '';
        $jornadaSeleccionada = null;
        if (in_array($diaSemana, ['sabado', 'domingo'], true) && $horaInicio === '07:00') {
            $jornadaSeleccionada = 'fin_semana';
        } else {
            foreach ($jornadas as $key => $j) {
                if ($horaInicio === $j['inicio']) {
                    $jornadaSeleccionada = $key;
                    break;
                }
            }
        }

        return view('reservas.edit', [
            'reserva' => $reserva,
            'ambientes' => $ambientes,
            'fichas' => $fichas,
            'estados' => $estados,
            'instructores' => $instructores,
            'jornadas' => $jornadas,
            'jornadaSeleccionada' => $jornadaSeleccionada
        ]);
    }

    /**
     * Update the specified reservation in storage.
     */
    public function update(UpdateReservaRequest $request, $id)
    {
        // La validación se realiza automáticamente por el Form Request

        // Validar capacidad máxima del ambiente
        $ambiente = DB::table('ambientes')
            ->where('id_ambiente', $request->id_ambiente)
            ->first();
        
        if ($ambiente) {
            // Obtener capacidad máxima del ambiente (por defecto 35 si no está definida)
            $capacidadMaxima = $ambiente->capacidad_max ?? 35;
            
            // Obtener cantidad de aprendices de la ficha
            $ficha = DB::table('ficha')
                ->where('id_ficha', $request->id_ficha)
                ->first();
            
            if ($ficha && $ficha->cant_aprendices > $capacidadMaxima) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', "La cantidad de aprendices ({$ficha->cant_aprendices}) excede la capacidad máxima del ambiente ({$capacidadMaxima} aprendices).");
            }
        }

        // Validar que no haya solapamiento de horarios con reservas activas (excluyendo la reserva actual)
        // Solo verificar reservas activas (1), excluyendo finalizadas (3) y canceladas (2)
        $conflictos = DB::table('reservas')
            ->where('id_ambiente', $request->id_ambiente)
            ->where('dia_semana', $request->dia_semana)
            ->where('id_estado_reserva', 1) // Solo verificar reservas activas (1)
            ->where('id_reserva', '!=', $id) // Excluir la reserva que se está editando
            ->where(function($query) use ($request) {
                // Verificar solapamiento: dos intervalos se solapan si 
                // inicio1 < fin2 AND fin1 > inicio2
                $query->where(function($q) use ($request) {
                    $q->where('hora_inicio', '<', $request->hora_fin)
                      ->where('hora_fin', '>', $request->hora_inicio);
                });
            })
            ->exists();

        if ($conflictos) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ya existe una reserva activa en este ambiente para el día y horario seleccionado. Por favor, elige otro horario o día.');
        }

        $reserva = Reserva::find($id);
        if (!$reserva) {
            return redirect()->route('ambientes.index')->with('error', 'Reserva no encontrada.');
        }

        $idAmbienteAnterior = $reserva->id_ambiente;
        
        $reserva->update([
            'id_ambiente' => $request->id_ambiente,
            'id_ficha' => $request->id_ficha,
            'id_persona' => $request->id_persona ?: null,
            'dia_semana' => $request->dia_semana,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'id_estado_reserva' => $request->id_estado_reserva,
            'observaciones' => $request->observaciones ?? null,
        ]);

        // Registrar auditoría de actualización de reserva
        try {
            $user = Auth::user();
            SecurityAuditLog::create([
                'user_id'       => $user?->id_cedula,
                'action'        => 'reserva_updated',
                'resource_type' => 'reserva',
                'resource_id'   => $reserva->id_reserva ?? $id,
                'description'   => 'Actualización de reserva ID ' . $id,
                'ip_address'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // No romper el flujo si falla el log
        }

        // Actualizar el estado del ambiente nuevo (si cambió)
        $this->actualizarEstadoAmbiente($request->id_ambiente);
        
        // Si cambió de ambiente, actualizar también el ambiente anterior
        if ($idAmbienteAnterior != $request->id_ambiente) {
            $this->actualizarEstadoAmbiente($idAmbienteAnterior);
        }

        return redirect()->route('ambientes.index')->with('success', 'Reserva actualizada correctamente.');
    }

    /**
     * Remove the specified reservation from storage.
     */
    public function destroy($id)
    {
        try {
            $reserva = Reserva::find($id);
            
            if (!$reserva) {
                return redirect()->route('ambientes.index')->with('error', 'Reserva no encontrada.');
            }

            $idAmbiente = $reserva->id_ambiente;
            $idReserva = $reserva->id_reserva;
            $reserva->delete();

            // Actualizar el estado del ambiente después de eliminar la reserva
            $this->actualizarEstadoAmbiente($idAmbiente);

            // Registrar auditoría de eliminación de reserva
            try {
                $user = Auth::user();
                SecurityAuditLog::create([
                    'user_id'       => $user?->id_cedula,
                    'action'        => 'reserva_deleted',
                    'resource_type' => 'reserva',
                    'resource_id'   => $idReserva ?? $id,
                    'description'   => 'Eliminación de reserva ID ' . $id,
                    'ip_address'    => $request->ip(),
                    'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                    'status'        => 'success',
                    'metadata'      => null,
                    'created_at'    => now(),
                ]);
            } catch (\Throwable $e) {
                // No romper el flujo si falla el log
            }

            return redirect()->route('ambientes.index')->with('success', 'Reserva eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar reserva: ' . $e->getMessage());
            return redirect()->route('ambientes.index')->with('error', 'Error al eliminar la reserva. Por favor, inténtalo de nuevo.');
        }
    }
}

