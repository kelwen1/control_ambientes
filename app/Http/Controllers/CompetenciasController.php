<?php

namespace App\Http\Controllers;

use App\Helpers\EliminacionDependenciasHelper;
use App\Models\Competencia;
use App\Support\ExcelHtmlExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompetenciasController extends Controller
{
    public function index(Request $request)
    {
        $query = Competencia::query()->orderBy('nombre_competencia');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('nombre_competencia', 'like', '%'.$search.'%');
        }

        $competencias = $query->paginate(10)->appends($request->query());

        return view('competencias.index', [
            'competencias' => $competencias,
            'search' => $request->input('search', ''),
        ]);
    }

    public function create()
    {
        return view('competencias.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'hora_totales' => preg_replace('/\D/', '', (string) $request->input('hora_totales', '')),
            'porcentaje_horas' => substr(preg_replace('/\D/', '', (string) $request->input('porcentaje_horas', '')), 0, 2),
        ]);

        $validated = $request->validate([
            'nombre_competencia' => ['required', 'string', 'max:150', 'regex:/^[\p{L}\s]+$/u'],
            'nombre_norma' => ['required', 'string', 'max:150', 'regex:/^[\p{L}\s]+$/u'],
            'codigo' => ['required', 'regex:/^\d{1,9}$/'],
            'hora_totales' => ['required', 'regex:/^[0-9]+$/', 'integer', 'min:1', 'max:1500'],
            'porcentaje_horas' => ['required', 'regex:/^[0-9]{1,2}$/', 'integer', 'min:0', 'max:99'],
            'cantidad_resultados' => ['required', 'regex:/^\d+$/', 'integer', 'min:1', 'max:9'],
        ], [
            'nombre_competencia.required' => 'El nombre de la competencia es obligatorio.',
            'nombre_competencia.max' => 'El nombre de la competencia no puede superar 150 caracteres.',
            'nombre_competencia.regex' => 'El nombre de la competencia solo puede contener letras y espacios.',
            'nombre_norma.required' => 'El nombre de la norma es obligatorio.',
            'nombre_norma.max' => 'El nombre de la norma no puede superar 150 caracteres.',
            'nombre_norma.regex' => 'El nombre de la norma solo puede contener letras y espacios.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.regex' => 'El código solo puede contener números y hasta 9 dígitos.',
            'hora_totales.required' => 'Las horas totales son obligatorias.',
            'hora_totales.regex' => 'Las horas totales solo pueden contener números (sin letras ni símbolos).',
            'hora_totales.integer' => 'Las horas totales deben ser un número entero.',
            'hora_totales.max' => 'Las horas totales no pueden ser mayores a 1500.',
            'porcentaje_horas.required' => 'El porcentaje de horas es obligatorio.',
            'porcentaje_horas.regex' => 'El porcentaje solo puede tener números (máximo 2 dígitos).',
            'porcentaje_horas.max' => 'El porcentaje no puede ser mayor a 99.',
            'cantidad_resultados.required' => 'La cantidad de resultados es obligatoria.',
            'cantidad_resultados.regex' => 'La cantidad de resultados solo puede contener números.',
            'cantidad_resultados.max' => 'La cantidad de resultados no puede ser mayor a 9.',
        ]);

        $horaTotales = (int) $validated['hora_totales'];
        $porcentaje = $this->clampPorcentajeHoras((int) $validated['porcentaje_horas']);
        $duracionEnComplejo = $this->calcularDuracionEnComplejo($horaTotales, $porcentaje);

        $payload = [
            'nombre_competencia' => $validated['nombre_competencia'],
            'id_programa' => null,
            'nombre_norma' => $validated['nombre_norma'],
            'codigo' => $validated['codigo'],
            'hora_totales' => $horaTotales,
            'porcentaje_horas' => $porcentaje,
            'duracion' => $duracionEnComplejo,
            'cantidad_resultados' => (int) $validated['cantidad_resultados'],
        ];

        $competencia = Competencia::create($payload);

        // Auditoría created_by / updated_by si existen las columnas
        $actorIdPersona = Auth::user()->persona->id_persona ?? null;
        if ($actorIdPersona !== null && Schema::hasColumn('competencia', 'created_by') && Schema::hasColumn('competencia', 'updated_by')) {
            DB::table('competencia')
                ->where('id_competencia', $competencia->id_competencia)
                ->update([
                    'created_by' => $actorIdPersona,
                    'updated_by' => $actorIdPersona,
                ]);
        }

        return redirect()->route('competencias.index')->with('success', 'Competencia creada correctamente.');
    }

    public function edit($id)
    {
        $competencia = Competencia::find($id);

        if (! $competencia) {
            return redirect()->route('competencias.index')->with('error', 'Competencia no encontrada.');
        }

        return view('competencias.edit', [
            'competencia' => $competencia,
        ]);
    }

    public function update(Request $request, $id)
    {
        $competencia = Competencia::find($id);

        if (! $competencia) {
            return redirect()->route('competencias.index')->with('error', 'Competencia no encontrada.');
        }

        $request->merge([
            'hora_totales' => preg_replace('/\D/', '', (string) $request->input('hora_totales', '')),
            'porcentaje_horas' => substr(preg_replace('/\D/', '', (string) $request->input('porcentaje_horas', '')), 0, 2),
        ]);

        $validated = $request->validate([
            'nombre_competencia' => ['required', 'string', 'max:150', 'regex:/^[\p{L}\s]+$/u'],
            'hora_totales' => ['required', 'regex:/^[0-9]+$/', 'integer', 'min:1', 'max:1500'],
            'porcentaje_horas' => ['required', 'regex:/^[0-9]{1,2}$/', 'integer', 'min:0', 'max:99'],
        ], [
            'nombre_competencia.required' => 'El nombre de la competencia es obligatorio.',
            'nombre_competencia.max' => 'El nombre de la competencia no puede superar 150 caracteres.',
            'nombre_competencia.regex' => 'El nombre de la competencia solo puede contener letras y espacios.',
            'hora_totales.required' => 'Las horas totales son obligatorias.',
            'hora_totales.regex' => 'Las horas totales solo pueden contener números (sin letras ni símbolos).',
            'hora_totales.integer' => 'Las horas totales deben ser un número entero.',
            'hora_totales.max' => 'Las horas totales no pueden ser mayores a 1500.',
            'porcentaje_horas.required' => 'El porcentaje de horas es obligatorio.',
            'porcentaje_horas.regex' => 'El porcentaje solo puede tener números (máximo 2 dígitos).',
            'porcentaje_horas.max' => 'El porcentaje no puede ser mayor a 99.',
        ]);

        $horaTotales = (int) $validated['hora_totales'];
        $porcentaje = $this->clampPorcentajeHoras((int) $validated['porcentaje_horas']);
        $duracionEnComplejo = $this->calcularDuracionEnComplejo($horaTotales, $porcentaje);

        $competencia->update([
            'nombre_competencia' => $validated['nombre_competencia'],
            'id_programa' => null,
            'hora_totales' => $horaTotales,
            'porcentaje_horas' => $porcentaje,
            'duracion' => $duracionEnComplejo,
        ]);

        // Auditoría updated_by si existe la columna
        $actorIdPersona = Auth::user()->persona->id_persona ?? null;
        if ($actorIdPersona !== null && Schema::hasColumn('competencia', 'updated_by')) {
            DB::table('competencia')
                ->where('id_competencia', $competencia->id_competencia)
                ->update(['updated_by' => $actorIdPersona]);
        }

        return redirect()->route('competencias.index')->with('success', 'Competencia actualizada correctamente.');
    }

    public function destroy($id)
    {
        $competencia = Competencia::find($id);

        if (! $competencia) {
            return redirect()->route('competencias.index')->with('error', 'Competencia no encontrada.');
        }

        $motivo = EliminacionDependenciasHelper::motivoNoEliminarCompetencia((int) $competencia->id_competencia);
        if ($motivo !== null) {
            return redirect()->route('competencias.index')->with('error', $motivo);
        }

        $competencia->delete();

        return redirect()->route('competencias.index')->with('success', 'Competencia eliminada correctamente.');
    }

    private function clampPorcentajeHoras(int $valor): int
    {
        if ($valor > 85) {
            return 85;
        }
        if ($valor < 60) {
            return 60;
        }

        return $valor;
    }

    /**
     * Horas impartidas en el complejo (porcentaje de horas totales).
     */
    private function calcularDuracionEnComplejo(int $horaTotales, int $porcentaje): int
    {
        if ($horaTotales < 1) {
            return 0;
        }

        return (int) round($horaTotales * ($porcentaje / 100));
    }

    public function export(Request $request)
    {
        if (Auth::user()->isInstructor()) {
            abort(403);
        }

        $competencias = $this->competenciasQueryParaExport($request)
            ->with('programa')
            ->get();

        $filename = 'competencias_'.date('Y-m-d_His').'.pdf';

        return app('dompdf.wrapper')->loadView('pdf.competencias', compact('competencias'))
            ->download($filename);
    }

    public function exportExcel(Request $request)
    {
        if (Auth::user()->isInstructor()) {
            abort(403);
        }

        $competencias = $this->competenciasQueryParaExport($request)
            ->with('programa')
            ->get();

        return ExcelHtmlExport::download('exports.competencias_excel', compact('competencias'), 'competencias');
    }

    private function competenciasQueryParaExport(Request $request)
    {
        $query = Competencia::query()->orderBy('nombre_competencia');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('nombre_competencia', 'like', '%'.$search.'%');
        }

        return $query;
    }
}
