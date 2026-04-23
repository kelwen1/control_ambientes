<?php

namespace App\Http\Controllers;

use App\Helpers\EliminacionDependenciasHelper;
use App\Helpers\FichaProgramaDuracionHelper;
use App\Helpers\SearchHelper;
use App\Models\Programa;
use App\Support\ExcelHtmlExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProgramasController extends Controller
{
    public function index(Request $request)
    {
        $query = Programa::query()->orderBy('nombre_programa');

        $rawSearch = trim((string) $request->input('search', ''));
        $search = $rawSearch === '' ? '' : preg_replace('/[^\p{L}\s]/u', '', $rawSearch);
        if ($search !== '') {
            $like = SearchHelper::escapeLikeSpecialChars($search);
            $query->where('nombre_programa', 'like', '%'.$like.'%');
        }

        $programas = $query->paginate(10)->withQueryString();

        $niveles = DB::table('nivel_programa')
            ->orderBy('id_nivel_programa')
            ->pluck('nivel_programa', 'id_nivel_programa')
            ->toArray();

        $duraciones = DB::table('duracion')
            ->orderBy('id_duracion')
            ->pluck('duracion', 'id_duracion')
            ->toArray();

        return view('programas.index', [
            'programas' => $programas,
            'search' => $search,
            'niveles' => $niveles,
            'duraciones' => $duraciones,
        ]);
    }

    public function create()
    {
        $niveles = DB::table('nivel_programa')
            ->orderBy('id_nivel_programa')
            ->pluck('nivel_programa', 'id_nivel_programa')
            ->toArray();

        $duraciones = DB::table('duracion')
            ->orderBy('id_duracion')
            ->pluck('duracion', 'id_duracion')
            ->toArray();

        $nivelDuracionIds = [];
        foreach (array_keys($niveles) as $idNivel) {
            $idDur = FichaProgramaDuracionHelper::idDuracionPorNivelId((int) $idNivel);
            if ($idDur !== null) {
                $nivelDuracionIds[(int) $idNivel] = $idDur;
            }
        }

        return view('programas.create', [
            'niveles' => $niveles,
            'duraciones' => $duraciones,
            'nivelDuracionIds' => $nivelDuracionIds,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_programa' => [
                'required',
                'string',
                'max:100',
                'regex:/^[\p{L}\s]+$/u',
                'unique:programa,nombre_programa',
            ],
            'id_nivel_programa' => 'required|integer|exists:nivel_programa,id_nivel_programa',
        ], [
            'nombre_programa.required' => 'El nombre del programa es obligatorio.',
            'nombre_programa.unique' => 'Ya existe un programa con ese nombre.',
            'nombre_programa.regex' => 'El nombre solo puede contener letras y espacios.',
            'id_nivel_programa.required' => 'Debe seleccionar un nivel de programa.',
        ]);

        $idDuracion = FichaProgramaDuracionHelper::idDuracionPorNivelId((int) $validated['id_nivel_programa']);
        if ($idDuracion === null) {
            return back()
                ->withErrors([
                    'id_nivel_programa' => 'No hay duración asociada a este nivel. En «Niveles de programa», edite el nivel y defina los meses, o use un nombre que el sistema reconozca (p. ej. tecnología, técnica, media técnica).',
                ])
                ->withInput();
        }

        $programa = Programa::create([
            'nombre_programa' => $validated['nombre_programa'],
            'id_nivel_programa' => $validated['id_nivel_programa'],
            'id_duracion' => $idDuracion,
        ]);

        // Auditoría created_by / updated_by si existen las columnas
        $actorIdPersona = Auth::user()->persona->id_persona ?? null;
        if ($actorIdPersona !== null && Schema::hasColumn('programa', 'created_by') && Schema::hasColumn('programa', 'updated_by')) {
            DB::table('programa')
                ->where('id_programa', $programa->id_programa)
                ->update([
                    'created_by' => $actorIdPersona,
                    'updated_by' => $actorIdPersona,
                ]);
        }

        return redirect()->route('programas.index')->with('success', 'Programa creado correctamente.');
    }

    public function edit($id)
    {
        $programa = Programa::find($id);

        if (! $programa) {
            return redirect()->route('programas.index')->with('error', 'Programa no encontrado.');
        }

        $niveles = DB::table('nivel_programa')
            ->orderBy('id_nivel_programa')
            ->pluck('nivel_programa', 'id_nivel_programa')
            ->toArray();

        $duraciones = DB::table('duracion')
            ->orderBy('id_duracion')
            ->pluck('duracion', 'id_duracion')
            ->toArray();

        return view('programas.edit', [
            'programa' => $programa,
            'nivelEtiqueta' => $niveles[$programa->id_nivel_programa] ?? '—',
            'duracionEtiqueta' => $duraciones[$programa->id_duracion] ?? '—',
        ]);
    }

    public function update(Request $request, $id)
    {
        $programa = Programa::find($id);

        if (! $programa) {
            return redirect()->route('programas.index')->with('error', 'Programa no encontrado.');
        }

        $validated = $request->validate([
            'nombre_programa' => [
                'required',
                'string',
                'max:100',
                'regex:/^[\p{L}\s]+$/u',
                'unique:programa,nombre_programa,'.$programa->id_programa.',id_programa',
            ],
        ], [
            'nombre_programa.required' => 'El nombre del programa es obligatorio.',
            'nombre_programa.unique' => 'Ya existe un programa con ese nombre.',
            'nombre_programa.regex' => 'El nombre solo puede contener letras y espacios.',
        ]);

        $programa->update(['nombre_programa' => $validated['nombre_programa']]);

        // Auditoría updated_by si existe la columna
        $actorIdPersona = Auth::user()->persona->id_persona ?? null;
        if ($actorIdPersona !== null && Schema::hasColumn('programa', 'updated_by')) {
            DB::table('programa')
                ->where('id_programa', $programa->id_programa)
                ->update(['updated_by' => $actorIdPersona]);
        }

        return redirect()->route('programas.index')->with('success', 'Programa actualizado correctamente.');
    }

    public function destroy($id)
    {
        $programa = Programa::find($id);

        if (! $programa) {
            return redirect()->route('programas.index')->with('error', 'Programa no encontrado.');
        }

        $motivo = EliminacionDependenciasHelper::motivoNoEliminarPrograma((int) $programa->id_programa);
        if ($motivo !== null) {
            return redirect()->route('programas.index')->with('error', $motivo);
        }

        $programa->delete();

        return redirect()->route('programas.index')->with('success', 'Programa eliminado correctamente.');
    }

    public function export(Request $request)
    {
        if (Auth::user()->isInstructor()) {
            abort(403);
        }

        $programas = $this->programasQueryParaExport($request)->get();
        $niveles = DB::table('nivel_programa')
            ->orderBy('id_nivel_programa')
            ->pluck('nivel_programa', 'id_nivel_programa')
            ->toArray();
        $duraciones = DB::table('duracion')
            ->orderBy('id_duracion')
            ->pluck('duracion', 'id_duracion')
            ->toArray();

        $filename = 'programas_'.date('Y-m-d_His').'.pdf';

        return app('dompdf.wrapper')->loadView('pdf.programas', compact('programas', 'niveles', 'duraciones'))
            ->download($filename);
    }

    public function exportExcel(Request $request)
    {
        if (Auth::user()->isInstructor()) {
            abort(403);
        }

        $programas = $this->programasQueryParaExport($request)->get();
        $niveles = DB::table('nivel_programa')
            ->orderBy('id_nivel_programa')
            ->pluck('nivel_programa', 'id_nivel_programa')
            ->toArray();
        $duraciones = DB::table('duracion')
            ->orderBy('id_duracion')
            ->pluck('duracion', 'id_duracion')
            ->toArray();

        return ExcelHtmlExport::download('exports.programas_excel', compact('programas', 'niveles', 'duraciones'), 'programas');
    }

    private function programasQueryParaExport(Request $request)
    {
        $query = Programa::query()->orderBy('nombre_programa');

        $rawSearch = trim((string) $request->input('search', ''));
        $search = $rawSearch === '' ? '' : preg_replace('/[^\p{L}\s]/u', '', $rawSearch);
        if ($search !== '') {
            $like = SearchHelper::escapeLikeSpecialChars($search);
            $query->where('nombre_programa', 'like', '%'.$like.'%');
        }

        return $query;
    }
}
