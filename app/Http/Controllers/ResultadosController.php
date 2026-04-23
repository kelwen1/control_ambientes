<?php

namespace App\Http\Controllers;

use App\Helpers\EliminacionDependenciasHelper;
use App\Models\Competencia;
use App\Models\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ResultadosController extends Controller
{
    public function index(Request $request)
    {
        $query = Resultado::with('competencia')->orderBy('denominacion');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('denominacion', 'like', '%'.$search.'%');
        }

        if ($request->filled('competencia')) {
            $query->where('id_competencia', $request->input('competencia'));
        }

        $resultados = $query->paginate(10)->appends($request->query());
        $competencias = Competencia::orderBy('nombre_competencia')->get(['id_competencia', 'nombre_competencia']);
        $competenciaSeleccionada = $request->input('competencia');
        $filtroFijoCompetencia = $request->filled('competencia');
        $competenciaFija = $filtroFijoCompetencia
            ? Competencia::find($competenciaSeleccionada, ['id_competencia', 'nombre_competencia'])
            : null;

        return view('resultados.index', [
            'resultados' => $resultados,
            'competencias' => $competencias,
            'search' => $request->input('search', ''),
            'competenciaSeleccionada' => $competenciaSeleccionada,
            'filtroFijoCompetencia' => $filtroFijoCompetencia,
            'competenciaFija' => $competenciaFija,
        ]);
    }

    public function create($competencia = null)
    {
        $idCompetencia = null;
        if ($competencia !== null && $competencia !== '') {
            $idCompetencia = (int) $competencia;
        } elseif (request()->filled('competencia')) {
            $idCompetencia = (int) request()->input('competencia');
        }

        $competenciaPreseleccionada = null;
        $horasRestantesPreseleccion = null;
        if ($idCompetencia) {
            $competenciaPreseleccionada = Competencia::query()->find($idCompetencia);
            if ($competenciaPreseleccionada) {
                $durComp = $competenciaPreseleccionada->horasDuracionEnComplejo();
                $usadas = (int) Resultado::where('id_competencia', $competenciaPreseleccionada->id_competencia)->sum('horas');
                $horasRestantesPreseleccion = max(0, $durComp - $usadas);
            }
        }

        $competencias = Competencia::query()
            ->orderBy('nombre_competencia')
            ->get();

        $resultadosPorCompetencia = Resultado::whereIn('id_competencia', $competencias->pluck('id_competencia'))
            ->selectRaw('id_competencia, COUNT(*) as total')
            ->groupBy('id_competencia')
            ->pluck('total', 'id_competencia');

        $horasUsadasPorCompetencia = Resultado::query()
            ->whereIn('id_competencia', $competencias->pluck('id_competencia'))
            ->selectRaw('id_competencia, COALESCE(SUM(horas), 0) as total_horas')
            ->groupBy('id_competencia')
            ->pluck('total_horas', 'id_competencia');

        return view('resultados.create', [
            'competencias' => $competencias,
            'resultadosPorCompetencia' => $resultadosPorCompetencia,
            'horasUsadasPorCompetencia' => $horasUsadasPorCompetencia,
            'competenciaPreseleccionada' => $competenciaPreseleccionada,
            'horasRestantesPreseleccion' => $horasRestantesPreseleccion,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'denominacion' => ['required', 'string', 'max:150', 'regex:/^[\p{L}\s]+$/u'],
            'id_competencia' => [
                'required',
                'integer',
                Rule::exists('competencia', 'id_competencia'),
            ],
            'horas' => ['required', 'integer', 'min:1', 'max:9999999'],
        ], [
            'denominacion.required' => 'La denominación del resultado es obligatoria.',
            'denominacion.max' => 'La denominación no puede superar los 150 caracteres.',
            'denominacion.regex' => 'La denominación solo puede contener letras y espacios.',
            'horas.required' => 'Las horas son obligatorias.',
            'horas.integer' => 'Las horas deben ser un número entero.',
            'horas.max' => 'El valor de horas es demasiado grande.',
            'id_competencia.required' => 'Debe seleccionar una competencia.',
            'id_competencia.exists' => 'La competencia seleccionada no es válida.',
        ]);

        $competencia = Competencia::find($validated['id_competencia']);
        if (! $competencia) {
            return redirect()->back()->withInput()->withErrors(['id_competencia' => 'Competencia no encontrada.']);
        }

        $cantidadResultados = (int) ($competencia->cantidad_resultados ?? 0);

        // Validar que no se supere la cantidad de resultados permitidos
        if ($cantidadResultados > 0) {
            $resultadosActuales = Resultado::where('id_competencia', $competencia->id_competencia)->count();
            if ($resultadosActuales >= $cantidadResultados) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'id_competencia' => 'La competencia "'.$competencia->nombre_competencia.'" ya tiene el máximo de resultados permitidos ('.$cantidadResultados.'). No se pueden crear más.',
                    ]);
            }
        }

        $horas = (int) $validated['horas'];
        $duracionComplejo = $competencia->horasDuracionEnComplejo();
        if ($duracionComplejo < 1) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'horas' => 'La competencia no tiene horas de duración en el complejo definidas; no se pueden asignar horas al resultado.',
                ]);
        }

        $horasUsadasOtras = (int) Resultado::where('id_competencia', $competencia->id_competencia)->sum('horas');
        $horasRestantes = $duracionComplejo - $horasUsadasOtras;

        $sesiones = intdiv($horas, 6);

        if ($horas > $horasRestantes) {
            $usadas = $duracionComplejo - $horasRestantes;

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'horas' => 'Solo quedan '.$horasRestantes.' h libres en el complejo para nuevos resultados ('.$duracionComplejo.' h totales; '.$usadas.' h ya asignadas a otros). El máximo que puede poner en este resultado es '.$horasRestantes.' h.',
                ]);
        }

        $validated['horas'] = $horas;
        $validated['sesiones'] = $sesiones;

        $resultado = Resultado::create($validated);

        // Auditoría created_by / updated_by si existen las columnas
        $actorIdPersona = Auth::user()->persona->id_persona ?? null;
        if ($actorIdPersona !== null && Schema::hasColumn('resultados', 'created_by') && Schema::hasColumn('resultados', 'updated_by')) {
            DB::table('resultados')
                ->where('id_resultado', $resultado->id_resultado)
                ->update([
                    'created_by' => $actorIdPersona,
                    'updated_by' => $actorIdPersona,
                ]);
        }

        return redirect()
            ->route('resultados.index', ['competencia' => $resultado->id_competencia])
            ->with('success', 'Resultado creado correctamente.');
    }

    public function edit($id)
    {
        $resultado = Resultado::with('competencia')->find($id);

        if (! $resultado) {
            return redirect()->route('resultados.index')->with('error', 'Resultado no encontrado.');
        }

        $duracionComplejo = $resultado->competencia->horasDuracionEnComplejo();
        $horasUsadasOtras = (int) Resultado::where('id_competencia', $resultado->id_competencia)
            ->where('id_resultado', '!=', $resultado->id_resultado)
            ->sum('horas');
        $horasRestantes = max(0, $duracionComplejo - $horasUsadasOtras);
        $sesionesTotalesCompetencia = $duracionComplejo > 0 ? intdiv($duracionComplejo, 6) : 0;

        return view('resultados.edit', [
            'resultado' => $resultado,
            'duracionComplejo' => $duracionComplejo,
            'horasRestantes' => $horasRestantes,
            'sesionesTotalesCompetencia' => $sesionesTotalesCompetencia,
        ]);
    }

    public function update(Request $request, $id)
    {
        $resultado = Resultado::find($id);

        if (! $resultado) {
            return redirect()->route('resultados.index')->with('error', 'Resultado no encontrado.');
        }

        $competencia = Competencia::find($resultado->id_competencia);
        if (! $competencia) {
            return redirect()->route('resultados.index')->with('error', 'Competencia no encontrada.');
        }

        $validated = $request->validate([
            'denominacion' => ['required', 'string', 'max:150', 'regex:/^[\p{L}\s]+$/u'],
            'horas' => ['required', 'integer', 'min:1', 'max:9999999'],
        ], [
            'denominacion.required' => 'La denominación del resultado es obligatoria.',
            'denominacion.max' => 'La denominación no puede superar los 150 caracteres.',
            'denominacion.regex' => 'La denominación solo puede contener letras y espacios.',
            'horas.required' => 'Las horas son obligatorias.',
            'horas.integer' => 'Las horas deben ser un número entero.',
            'horas.max' => 'El valor de horas es demasiado grande.',
        ]);

        $horas = (int) $validated['horas'];
        $duracionComplejo = $competencia->horasDuracionEnComplejo();
        if ($duracionComplejo < 1) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'horas' => 'La competencia no tiene horas de duración en el complejo definidas.',
                ]);
        }

        $horasUsadasOtras = (int) Resultado::where('id_competencia', $competencia->id_competencia)
            ->where('id_resultado', '!=', $resultado->id_resultado)
            ->sum('horas');
        $horasRestantes = $duracionComplejo - $horasUsadasOtras;

        $sesiones = intdiv($horas, 6);

        if ($horas > $horasRestantes) {
            $enOtros = $duracionComplejo - $horasRestantes;

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'horas' => 'Para este resultado el máximo es '.$horasRestantes.' h ('.$duracionComplejo.' h totales en el complejo; '.$enOtros.' h ya en otros resultados). No puede superar ese límite.',
                ]);
        }

        $resultado->update([
            'denominacion' => $validated['denominacion'],
            'horas' => $horas,
            'sesiones' => $sesiones,
        ]);

        // Auditoría updated_by si existe la columna
        $actorIdPersona = Auth::user()->persona->id_persona ?? null;
        if ($actorIdPersona !== null && Schema::hasColumn('resultados', 'updated_by')) {
            DB::table('resultados')
                ->where('id_resultado', $resultado->id_resultado)
                ->update(['updated_by' => $actorIdPersona]);
        }

        return redirect()
            ->route('resultados.index', ['competencia' => $resultado->id_competencia])
            ->with('success', 'Resultado actualizado correctamente.');
    }

    public function destroy($id)
    {
        $resultado = Resultado::find($id);

        if (! $resultado) {
            return redirect()->route('resultados.index')->with('error', 'Resultado no encontrado.');
        }

        $idCompetencia = (int) $resultado->id_competencia;

        $motivo = EliminacionDependenciasHelper::motivoNoEliminarResultado((int) $resultado->id_resultado);
        if ($motivo !== null) {
            return redirect()
                ->route('resultados.index', ['competencia' => $idCompetencia])
                ->with('error', $motivo);
        }

        $resultado->delete();

        return redirect()
            ->route('resultados.index', ['competencia' => $idCompetencia])
            ->with('success', 'Resultado eliminado correctamente.');
    }
}
