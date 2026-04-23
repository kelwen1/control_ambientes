<?php

namespace App\Http\Controllers;

use App\Helpers\FestivosColombiaHelper;
use App\Models\Reserva;
use App\Models\ReservaDiaLiberado;
use App\Support\SecureRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Liberar / recuperar días y festivos: solo administrador o coordinación L.
 */
class ReservaLiberacionController extends Controller
{
    public function liberarDia(Request $request)
    {
        $user = Auth::user();
        if (! $user || (! $user->isAdmin() && ! $user->isCoordinatorL())) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para esta acción.');
        }

        $request->validate([
            'id_reserva' => 'required|integer|exists:reservas,id_reserva',
            'fecha' => 'required|date',
        ]);

        $reserva = Reserva::where('id_reserva', $request->id_reserva)
            ->where('id_estado_reserva', 1)
            ->first();

        if (! $reserva) {
            return redirect()->back()->with('error', 'Reserva no encontrada o no está activa.');
        }

        $fecha = \Carbon\Carbon::parse($request->fecha)->startOfDay();
        $fechaInicio = \Carbon\Carbon::parse($reserva->fecha_inicio)->startOfDay();
        $fechaFin = \Carbon\Carbon::parse($reserva->fecha_fin)->startOfDay();

        if ($fecha->lt($fechaInicio) || $fecha->gt($fechaFin)) {
            return redirect()->back()->with('error', 'La fecha debe estar dentro del rango de la reserva (inclusive).');
        }

        $rowDia = DB::table('dia_semana')->where('id_dia_semana', $reserva->id_dia_semana)->first();
        $diaTexto = strtolower(trim($rowDia->dia_semana ?? ''));
        $diasMap = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 0];
        $targetDow = $diasMap[$diaTexto] ?? null;
        if ($targetDow !== null && $fecha->dayOfWeek !== $targetDow) {
            return redirect()->back()->with('error', 'La fecha debe ser un '.ucfirst($diaTexto).' (día de la reserva).');
        }

        if (ReservaDiaLiberado::where('id_reserva', $reserva->id_reserva)->where('fecha', $fecha->format('Y-m-d'))->exists()) {
            return redirect()->back()->with('info', 'Ese día ya estaba liberado.');
        }

        ReservaDiaLiberado::create([
            'id_reserva' => $reserva->id_reserva,
            'fecha' => $fecha->format('Y-m-d'),
        ]);

        $ruta = SecureRedirect::safeUrl($request->input('redirect'), 'dashboard');

        return redirect($ruta)->with('success', 'Día liberado correctamente. Otro instructor puede reservar ese ambiente para esa fecha.');
    }

    public function liberarFestivos(Request $request)
    {
        $user = Auth::user();
        if (! $user || (! $user->isAdmin() && ! $user->isCoordinatorL())) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para esta acción.');
        }

        $request->validate([
            'id_reserva' => 'required|integer|exists:reservas,id_reserva',
        ]);

        $reserva = Reserva::where('id_reserva', $request->id_reserva)
            ->where('id_estado_reserva', 1)
            ->first();

        if (! $reserva) {
            return redirect()->back()->with('error', 'Reserva no encontrada o no está activa.');
        }

        $rowDia = DB::table('dia_semana')->where('id_dia_semana', $reserva->id_dia_semana)->first();
        $diaTexto = strtolower(trim($rowDia->dia_semana ?? ''));
        $diasMap = ['lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6, 'domingo' => 0];
        $targetDow = $diasMap[$diaTexto] ?? null;
        if ($targetDow === null) {
            return redirect()->back()->with('error', 'No se pudo determinar el día de la reserva.');
        }

        $festivos = FestivosColombiaHelper::festivosEnRango(
            $reserva->fecha_inicio,
            $reserva->fecha_fin,
            $targetDow
        );

        if ($festivos === []) {
            return redirect()->back()->with('info', 'No hay festivos de Colombia en el rango para este día de la semana.');
        }

        $yaLiberados = ReservaDiaLiberado::where('id_reserva', $reserva->id_reserva)
            ->whereIn('fecha', array_column($festivos, 'fecha'))
            ->pluck('fecha')
            ->map(fn ($f) => $f instanceof \DateTimeInterface ? $f->format('Y-m-d') : (string) $f)
            ->toArray();

        $liberados = 0;
        foreach ($festivos as $f) {
            $fechaStr = $f['fecha'];
            if (in_array($fechaStr, $yaLiberados, true)) {
                continue;
            }
            ReservaDiaLiberado::create([
                'id_reserva' => $reserva->id_reserva,
                'fecha' => $fechaStr,
            ]);
            $liberados++;
        }

        $ruta = SecureRedirect::safeUrl($request->input('redirect'), 'dashboard');
        if ($liberados > 0) {
            $msg = $liberados === 1
                ? '1 día festivo liberado correctamente.'
                : "{$liberados} días festivos liberados correctamente.";

            return redirect($ruta)->with('success', $msg);
        }

        return redirect($ruta)->with('info', 'Todos los festivos de esta reserva ya estaban liberados.');
    }

    public function revertirDiaLiberado(Request $request)
    {
        $user = Auth::user();
        if (! $user || (! $user->isAdmin() && ! $user->isCoordinatorL())) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para esta acción.');
        }

        $request->validate([
            'id_reserva' => 'required|integer|exists:reservas,id_reserva',
            'fecha' => 'required|date',
        ]);

        $reserva = Reserva::where('id_reserva', $request->id_reserva)
            ->where('id_estado_reserva', 1)
            ->first();

        if (! $reserva) {
            return redirect()->back()->with('error', 'Reserva no encontrada o no está activa.');
        }

        $fecha = $request->fecha;
        $idJornadaFicha = DB::table('ficha')->where('id_ficha', $reserva->id_ficha)->value('id_jornada');
        $otraOcupa = DB::table('reservas')
            ->join('ficha', 'reservas.id_ficha', '=', 'ficha.id_ficha')
            ->where('reservas.id_ambiente', $reserva->id_ambiente)
            ->where('reservas.id_dia_semana', $reserva->id_dia_semana)
            ->where('ficha.id_jornada', $idJornadaFicha)
            ->where('reservas.id_estado_reserva', 1)
            ->where('reservas.id_reserva', '!=', $reserva->id_reserva)
            ->where('reservas.fecha_inicio', '<=', $fecha)
            ->where('reservas.fecha_fin', '>=', $fecha)
            ->whereNotExists(function ($q) use ($fecha) {
                $q->select(DB::raw(1))
                    ->from('reserva_dias_liberados')
                    ->whereColumn('reserva_dias_liberados.id_reserva', 'reservas.id_reserva')
                    ->where('reserva_dias_liberados.fecha', $fecha);
            })
            ->exists();

        if ($otraOcupa) {
            return redirect()->back()->with('error', 'No se puede recuperar ese día: ya hay otra reserva activa en ese ambiente y horario.');
        }

        $deleted = ReservaDiaLiberado::where('id_reserva', $reserva->id_reserva)
            ->where('fecha', $request->fecha)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', 'Día liberado revertido.');
        }

        return redirect()->back()->with('info', 'Ese día no estaba liberado.');
    }
}
