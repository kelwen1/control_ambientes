<?php

namespace App\Http\Controllers;

use App\Helpers\EliminacionDependenciasHelper;
use App\Helpers\FichaProgramaDuracionHelper;
use App\Models\Ficha;
use App\Models\Jornada;
use App\Models\Programa;
use App\Models\SecurityAuditLog;
use App\Support\ExcelHtmlExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class FichasController extends Controller
{
    /**
     * Display a listing of the fichas.
     */
    public function index(Request $request)
    {
        $query = Ficha::with('programa')
            ->select('ficha.*');

        if ($request->filled('search')) {
            $search = \App\Helpers\SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where('num_ficha', 'like', '%'.$search.'%');
        }

        $fichas = $query->orderBy('id_ficha', 'desc')->paginate(10)->withQueryString();

        return view('fichas.index', [
            'fichas' => $fichas,
            'search' => $request->search ?? '',
        ]);
    }

    /**
     * Export fichas a PDF
     */
    public function export(Request $request)
    {
        if (Auth::user()->isInstructor()) {
            abort(403);
        }

        $fichas = $this->fichasCollectionParaExport($request);
        $filename = 'fichas_'.date('Y-m-d_His').'.pdf';

        return app('dompdf.wrapper')->loadView('pdf.fichas', compact('fichas'))
            ->download($filename);
    }

    /**
     * Export fichas a Excel (HTML .xls, mismo criterio de filtro que el PDF)
     */
    public function exportExcel(Request $request)
    {
        if (Auth::user()->isInstructor()) {
            abort(403);
        }

        $fichas = $this->fichasCollectionParaExport($request);

        return ExcelHtmlExport::download('exports.fichas_excel', compact('fichas'), 'fichas');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Ficha>
     */
    private function fichasCollectionParaExport(Request $request)
    {
        $query = Ficha::with('programa');

        if ($request->filled('search')) {
            $search = \App\Helpers\SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where('num_ficha', 'like', '%'.$search.'%');
        }

        return $query->orderBy('id_ficha', 'desc')->get();
    }

    /**
     * Show the form for creating a new ficha.
     */
    public function create()
    {
        $programas = Programa::select('id_programa', 'nombre_programa', 'id_nivel_programa')
            ->orderBy('nombre_programa')
            ->get();

        $jornadasRegistro = Schema::hasTable('jornada')
            ? Jornada::query()->orderBy('id_jornada')->get()
            : collect();

        return view('fichas.create', [
            'programas' => $programas,
            'jornadas' => config('jornadas'),
            'jornadasRegistro' => $jornadasRegistro,
        ]);
    }

    /**
     * Calcula fecha fin y fecha productiva (JSON) según nivel del programa y fecha de inicio.
     */
    public function fechasPorPrograma(Request $request)
    {
        $request->validate([
            'id_programa' => 'required|integer|exists:programa,id_programa',
            'fecha_inicio' => 'required|date',
        ]);

        $programa = Programa::query()
            ->select('id_programa', 'id_nivel_programa', 'id_duracion')
            ->findOrFail($request->integer('id_programa'));

        $calculado = $this->calcularFechasDesdeInicio($request->input('fecha_inicio'), $programa);

        if ($calculado === null) {
            return response()->json([
                'message' => 'No se pudo obtener la duración en meses del programa. Revise la duración asignada al programa o use un nivel reconocible (media técnica, técnica, tecnología).',
            ], 422);
        }

        return response()->json($calculado);
    }

    /**
     * @return array{fecha_fin: string, fecha_productiva: string}|null
     */
    private function calcularFechasDesdeInicio(string $fechaInicio, Programa $programa): ?array
    {
        $meses = FichaProgramaDuracionHelper::mesesParaPrograma($programa);
        if ($meses === null) {
            return null;
        }

        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $finTeorica = $inicio->copy()->addMonths($meses);
        $productivaTeorica = $finTeorica->copy()->subMonths(6);
        // Un día antes en cada una (cierre de etapa y salida a prácticas según regla institucional).
        $fin = $finTeorica->copy()->subDay();
        $productiva = $productivaTeorica->copy()->subDay();

        return [
            'fecha_fin' => $fin->toDateString(),
            'fecha_productiva' => $productiva->toDateString(),
        ];
    }

    /**
     * Store a newly created ficha in storage.
     */
    public function store(Request $request)
    {
        $reglasJornada = Schema::hasTable('jornada')
            ? ['required', 'integer', Rule::exists('jornada', 'id_jornada')]
            : ['required', 'integer', 'in:1,2,3,4'];

        $request->validate([
            'num_ficha' => ['required', 'regex:/^\d{1,8}$/', 'unique:ficha,num_ficha'],
            'cant_aprendices' => ['required', 'integer', 'min:20', 'max:100'],
            'id_programa' => 'required|integer|exists:programa,id_programa',
            'id_jornada' => $reglasJornada,
            'fecha_inicio' => 'required|date',
        ], [
            'num_ficha.required' => 'El número de ficha es obligatorio.',
            'num_ficha.regex' => 'El número de ficha solo puede contener dígitos (máximo 8).',
            'num_ficha.unique' => 'Ya existe una ficha con ese número.',
            'cant_aprendices.required' => 'La cantidad de aprendices es obligatoria.',
            'cant_aprendices.integer' => 'La cantidad de aprendices debe ser un número entero.',
            'cant_aprendices.min' => 'La cantidad de aprendices debe ser mayor o igual a 20.',
            'cant_aprendices.max' => 'La cantidad de aprendices no puede ser mayor a 100.',
            'id_programa.required' => 'El programa es obligatorio.',
            'id_programa.exists' => 'El programa seleccionado no es válido.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'id_jornada.required' => 'Debe seleccionar la jornada del grupo.',
            'id_jornada.exists' => 'La jornada seleccionada no existe en el catálogo.',
            'id_jornada.in' => 'La jornada seleccionada no es válida.',
        ]);

        $programa = Programa::query()
            ->select('id_programa', 'id_nivel_programa', 'id_duracion')
            ->findOrFail($request->integer('id_programa'));

        $fechas = $this->calcularFechasDesdeInicio($request->input('fecha_inicio'), $programa);
        if ($fechas === null) {
            return redirect()->back()
                ->withErrors(['id_programa' => 'No se pudo calcular fechas: asigne una duración válida al programa o use un nivel reconocible (media técnica, técnica, tecnología).'])
                ->withInput();
        }

        Ficha::create([
            'num_ficha' => (int) $request->num_ficha,
            'cant_aprendices' => (int) $request->cant_aprendices,
            'id_programa' => $request->id_programa,
            'id_jornada' => (int) $request->id_jornada,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $fechas['fecha_fin'],
            'fecha_productiva' => $fechas['fecha_productiva'],
        ]);

        // Auditoría: creación de ficha
        try {
            $user = Auth::user();
            $ultimaFicha = Ficha::latest('id_ficha')->first();
            SecurityAuditLog::create([
                'user_id' => $user?->id_cedula,
                'action' => 'ficha_created',
                'resource_type' => 'ficha',
                'resource_id' => $ultimaFicha?->id_ficha,
                'description' => 'Creación de ficha número '.$request->num_ficha,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'status' => 'success',
                'metadata' => null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // No interrumpir el flujo si falla el log
        }

        return redirect()->route('fichas.index')->with('success', 'Ficha creada correctamente.');
    }

    /**
     * Show the form for editing the specified ficha.
     */
    public function edit($id)
    {
        $ficha = Ficha::with(['programa', 'jornada'])->find($id);

        if (! $ficha) {
            return redirect()->route('fichas.index')->with('error', 'Ficha no encontrada.');
        }

        return view('fichas.edit', [
            'ficha' => $ficha,
            'jornadas' => config('jornadas'),
        ]);
    }

    /**
     * Update the specified ficha in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'cant_aprendices' => ['required', 'integer', 'min:20', 'max:100'],
            'num_ficha' => ['required', 'regex:/^\d{1,8}$/', Rule::unique('ficha', 'num_ficha')->ignore($id, 'id_ficha')],
            'id_programa' => 'required|integer|exists:programa,id_programa',
            'fecha_inicio' => 'required|date',
        ], [
            'cant_aprendices.required' => 'La cantidad de aprendices es obligatoria.',
            'num_ficha.unique' => 'Ya existe una ficha con ese número.',
            'cant_aprendices.integer' => 'La cantidad de aprendices debe ser un número entero.',
            'cant_aprendices.min' => 'La cantidad de aprendices debe ser mayor o igual a 20.',
            'cant_aprendices.max' => 'La cantidad de aprendices no puede ser mayor a 100.',
        ]);

        $ficha = Ficha::findOrFail($id);

        $fechaInicioDb = $ficha->fecha_inicio
            ? Carbon::parse($ficha->fecha_inicio)->format('Y-m-d')
            : null;

        if ((string) $request->input('num_ficha') !== (string) $ficha->num_ficha
            || (int) $request->input('id_programa') !== (int) $ficha->id_programa
            || $request->input('fecha_inicio') !== $fechaInicioDb) {
            return redirect()->back()
                ->with('error', 'Los datos de la ficha no coinciden. Vuelva a abrir el formulario de edición.')
                ->withInput();
        }

        $ficha->update([
            'cant_aprendices' => (int) $request->cant_aprendices,
        ]);

        // Auditoría: actualización de ficha
        try {
            $user = Auth::user();
            SecurityAuditLog::create([
                'user_id' => $user?->id_cedula,
                'action' => 'ficha_updated',
                'resource_type' => 'ficha',
                'resource_id' => $ficha->id_ficha,
                'description' => 'Actualización de cantidad de aprendices, ficha número '.$ficha->num_ficha.' (ID '.$ficha->id_ficha.')',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'status' => 'success',
                'metadata' => null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // No interrumpir el flujo si falla el log
        }

        return redirect()->route('fichas.index')->with('success', 'Ficha actualizada correctamente.');
    }

    /**
     * Remove the specified ficha from storage.
     */
    public function destroy($id)
    {
        try {
            $ficha = Ficha::find($id);

            if (! $ficha) {
                return redirect()->route('fichas.index')->with('error', 'Ficha no encontrada.');
            }

            $motivo = EliminacionDependenciasHelper::motivoNoEliminarFicha((int) $ficha->id_ficha);
            if ($motivo !== null) {
                return redirect()
                    ->route('fichas.index')
                    ->with('error', $motivo);
            }

            $numFicha = $ficha->num_ficha;
            $ficha->delete();

            // Auditoría: eliminación de ficha
            try {
                $user = Auth::user();
                SecurityAuditLog::create([
                    'user_id' => $user?->id_cedula,
                    'action' => 'ficha_deleted',
                    'resource_type' => 'ficha',
                    'resource_id' => $id,
                    'description' => 'Eliminación de ficha número '.$numFicha.' (ID '.$id.')',
                    'ip_address' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 255),
                    'status' => 'success',
                    'metadata' => null,
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // No interrumpir el flujo si falla el log
            }

            return redirect()->route('fichas.index')->with('success', 'Ficha eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar ficha: '.$e->getMessage());

            return redirect()->route('fichas.index')->with('error', 'Error al eliminar la ficha. Por favor, inténtalo de nuevo.');
        }
    }
}
