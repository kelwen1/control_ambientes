<?php

namespace App\Http\Controllers;

use App\Helpers\EliminacionDependenciasHelper;
use App\Models\Ambiente;
use App\Support\ExcelHtmlExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AmbientesCrudController extends Controller
{
    /**
     * Listado de ambientes (gestión).
     */
    public function index(Request $request)
    {
        $query = Ambiente::query()
            ->select('id_ambiente', 'num_ambiente', 'id_estado', 'capacidad_max', 'id_tipo_ambiente')
            ->orderByRaw('CAST(num_ambiente AS UNSIGNED), num_ambiente');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('num_ambiente', 'like', '%'.$search.'%');
        }

        $ambientes = $query->paginate(10)->withQueryString();

        return view('ambientes.gestion.index', [
            'ambientes' => $ambientes,
            'search' => $request->input('search', ''),
            'estados' => $this->getEstados(),
            'tipos' => $this->getTipos(),
        ]);
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        return view('ambientes.create', [
            'tipos' => $this->getTipos(),
        ]);
    }

    /**
     * Comprueba si el número de ambiente está libre (JSON para el formulario de creación).
     */
    public function verificarNumeroAmbiente(Request $request)
    {
        $request->validate([
            'num_ambiente' => ['required', 'regex:/^\d{1,2}$/'],
        ], [
            'num_ambiente.required' => 'Indique un número de ambiente.',
            'num_ambiente.regex' => 'El número solo puede tener 1 o 2 dígitos.',
        ]);

        $num = $request->input('num_ambiente');
        $existe = Ambiente::query()->where('num_ambiente', $num)->exists();

        if ($existe) {
            return response()->json([
                'disponible' => false,
                'mensaje' => 'Este número ya está en uso (no disponible). Elija otro.',
            ]);
        }

        return response()->json([
            'disponible' => true,
            'mensaje' => 'Número disponible. Puede registrar el ambiente.',
        ]);
    }

    /**
     * Guardar nuevo ambiente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'num_ambiente' => ['required', 'regex:/^\d{1,2}$/', 'unique:ambientes,num_ambiente'],
            'capacidad_max' => ['required', 'integer', 'min:30', 'max:40'],
            'id_tipo_ambiente' => 'required|integer|exists:tipo_ambiente,id_tipo_ambiente',
        ], [
            'num_ambiente.required' => 'El número de ambiente es obligatorio.',
            'num_ambiente.regex' => 'El número de ambiente debe tener 1 o 2 dígitos.',
            'num_ambiente.unique' => 'Ya existe un ambiente con ese número.',
            'capacidad_max.required' => 'La capacidad máxima es obligatoria.',
            'capacidad_max.integer' => 'La capacidad máxima debe ser un número entero.',
            'capacidad_max.min' => 'La capacidad debe ser mayor o igual a 30.',
            'capacidad_max.max' => 'La capacidad no puede ser mayor a 40.',
            'id_tipo_ambiente.required' => 'Debe seleccionar un tipo de ambiente.',
            'id_tipo_ambiente.exists' => 'El tipo de ambiente seleccionado no es válido.',
        ]);

        $ambiente = Ambiente::create([
            'num_ambiente' => $validated['num_ambiente'],
            'id_estado' => 1,
            'capacidad_max' => $validated['capacidad_max'],
            'id_tipo_ambiente' => $validated['id_tipo_ambiente'],
        ]);

        // Registrar quién creó el ambiente, si la tabla tiene columnas de auditoría
        $actorIdPersona = Auth::user()->persona->id_persona ?? null;
        if ($actorIdPersona !== null && Schema::hasColumn('ambientes', 'created_by') && Schema::hasColumn('ambientes', 'updated_by')) {
            DB::table('ambientes')
                ->where('id_ambiente', $ambiente->id_ambiente)
                ->update([
                    'created_by' => $actorIdPersona,
                    'updated_by' => $actorIdPersona,
                ]);
        }

        return redirect()
            ->route('ambientes.gestion.index')
            ->with('success', 'Ambiente creado correctamente.');
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit($id)
    {
        $ambiente = Ambiente::find($id);

        if (! $ambiente) {
            return redirect()
                ->route('ambientes.gestion.index')
                ->with('error', 'Ambiente no encontrado.');
        }

        return view('ambientes.edit', [
            'ambiente' => $ambiente,
            'estados' => $this->getEstados(),
            'tipos' => $this->getTipos(),
        ]);
    }

    /**
     * Actualizar ambiente.
     */
    public function update(Request $request, $id)
    {
        $ambiente = Ambiente::find($id);

        if (! $ambiente) {
            return redirect()
                ->route('ambientes.gestion.index')
                ->with('error', 'Ambiente no encontrado.');
        }

        $validated = $request->validate([
            'capacidad_max' => ['required', 'integer', 'min:30', 'max:40'],
        ], [
            'capacidad_max.required' => 'La capacidad máxima es obligatoria.',
            'capacidad_max.integer' => 'La capacidad máxima debe ser un número entero.',
            'capacidad_max.min' => 'La capacidad debe ser mayor o igual a 30.',
            'capacidad_max.max' => 'La capacidad no puede ser mayor a 40.',
        ]);

        $ambiente->update(['capacidad_max' => $validated['capacidad_max']]);

        // Registrar quién actualizó el ambiente, si existe la columna updated_by
        $actorIdPersona = Auth::user()->persona->id_persona ?? null;
        if ($actorIdPersona !== null && Schema::hasColumn('ambientes', 'updated_by')) {
            DB::table('ambientes')
                ->where('id_ambiente', $ambiente->id_ambiente)
                ->update([
                    'updated_by' => $actorIdPersona,
                ]);
        }

        return redirect()
            ->route('ambientes.gestion.index')
            ->with('success', 'Ambiente actualizado correctamente.');
    }

    /**
     * Eliminar ambiente (si no tiene reservas asociadas).
     */
    public function destroy($id)
    {
        $ambiente = Ambiente::find($id);

        if (! $ambiente) {
            return redirect()
                ->route('ambientes.gestion.index')
                ->with('error', 'Ambiente no encontrado.');
        }

        $motivo = EliminacionDependenciasHelper::motivoNoEliminarAmbiente((int) $ambiente->id_ambiente);
        if ($motivo !== null) {
            return redirect()
                ->route('ambientes.gestion.index')
                ->with('error', $motivo);
        }

        $ambiente->delete();

        return redirect()
            ->route('ambientes.gestion.index')
            ->with('success', 'Ambiente eliminado correctamente.');
    }

    /**
     * Mapa de estados de ambiente.
     *
     * @return array<int, string>
     */
    private function getEstados(): array
    {
        return [
            1 => 'Disponible',
            2 => 'Mantenimiento',
            3 => 'Ocupado',
        ];
    }

    /**
     * Mapa de tipos de ambiente (convencional, especializado, mixto, etc.).
     *
     * @return array<int, string>
     */
    private function getTipos(): array
    {
        return DB::table('tipo_ambiente')
            ->orderBy('id_tipo_ambiente')
            ->pluck('nombre_tipo', 'id_tipo_ambiente')
            ->toArray();
    }

    public function export(Request $request)
    {
        if (Auth::user()->isInstructor()) {
            abort(403);
        }

        $ambientes = $this->ambientesGestionQueryParaExport($request)->get();
        $estados = $this->getEstados();
        $tipos = $this->getTipos();

        $filename = 'ambientes_catalogo_'.date('Y-m-d_His').'.pdf';

        return app('dompdf.wrapper')->loadView('pdf.ambientes_gestion', compact('ambientes', 'estados', 'tipos'))
            ->download($filename);
    }

    public function exportExcel(Request $request)
    {
        if (Auth::user()->isInstructor()) {
            abort(403);
        }

        $ambientes = $this->ambientesGestionQueryParaExport($request)->get();
        $estados = $this->getEstados();
        $tipos = $this->getTipos();

        return ExcelHtmlExport::download('exports.ambientes_gestion_excel', compact('ambientes', 'estados', 'tipos'), 'ambientes_catalogo');
    }

    private function ambientesGestionQueryParaExport(Request $request)
    {
        $query = Ambiente::query()
            ->select('id_ambiente', 'num_ambiente', 'id_estado', 'capacidad_max', 'id_tipo_ambiente')
            ->orderByRaw('CAST(num_ambiente AS UNSIGNED), num_ambiente');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('num_ambiente', 'like', '%'.$search.'%');
        }

        return $query;
    }
}
