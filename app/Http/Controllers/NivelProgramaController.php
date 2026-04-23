<?php

namespace App\Http\Controllers;

use App\Helpers\FichaProgramaDuracionHelper;
use App\Models\Duracion;
use App\Models\NivelPrograma;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class NivelProgramaController extends Controller
{
    public function index()
    {
        $niveles = NivelPrograma::query()
            ->orderBy('nivel_programa')
            ->get();

        $conProgramas = Programa::query()
            ->selectRaw('id_nivel_programa, COUNT(*) as c')
            ->groupBy('id_nivel_programa')
            ->pluck('c', 'id_nivel_programa')
            ->toArray();

        return view('niveles-programa.index', [
            'niveles' => $niveles,
            'conProgramas' => $conProgramas,
        ]);
    }

    public function create(Request $request)
    {
        return view('niveles-programa.create', [
            'desdePrograma' => $request->input('desde') === 'programa',
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'nivel_programa' => mb_strtolower(trim((string) $request->input('nivel_programa', '')), 'UTF-8'),
        ]);

        $validated = $request->validate([
            'nivel_programa' => [
                'required',
                'string',
                'max:120',
                'regex:/^[\p{L}\s]+$/u',
                Rule::unique('nivel_programa', 'nivel_programa'),
            ],
            'meses' => 'required|integer|min:1|max:120',
        ], [
            'nivel_programa.required' => 'El nombre del nivel es obligatorio.',
            'nivel_programa.unique' => 'Ya existe un nivel con ese nombre.',
            'nivel_programa.regex' => 'El nombre solo puede contener letras y espacios.',
            'meses.required' => 'Indique cuántos meses asociará a este nivel (se registra en la tabla duracion).',
            'meses.min' => 'La duración debe ser de al menos 1 mes.',
            'meses.max' => 'La duración no puede superar 120 meses.',
        ]);

        $nivel = DB::transaction(function () use ($validated) {
            $idDur = Duracion::idForMeses((int) $validated['meses']);
            $nivel = NivelPrograma::create([
                'nivel_programa' => $validated['nivel_programa'],
            ]);
            if (Schema::hasColumn('nivel_programa', 'id_duracion')) {
                DB::table('nivel_programa')
                    ->where('id_nivel_programa', $nivel->id_nivel_programa)
                    ->update(['id_duracion' => $idDur]);
            }

            return $nivel;
        });

        if ($request->input('desde') === 'programa') {
            return redirect()
                ->route('programas.create', ['nivel' => $nivel->id_nivel_programa])
                ->with('success', 'Nivel creado. La duración del programa se fijará sola al elegir este nivel.');
        }

        return redirect()
            ->route('niveles-programa.index')
            ->with('success', 'Nivel de programa creado correctamente.');
    }

    public function edit($id)
    {
        $nivel = NivelPrograma::query()->find($id);
        if (! $nivel) {
            return redirect()->route('niveles-programa.index')->with('error', 'Nivel no encontrado.');
        }

        $mesesActual = FichaProgramaDuracionHelper::mesesPorNivelId((int) $nivel->id_nivel_programa);

        return view('niveles-programa.edit', [
            'nivel' => $nivel,
            'mesesActual' => $mesesActual,
        ]);
    }

    public function update(Request $request, $id)
    {
        $nivel = NivelPrograma::find($id);
        if (! $nivel) {
            return redirect()->route('niveles-programa.index')->with('error', 'Nivel no encontrado.');
        }

        $request->merge([
            'nivel_programa' => mb_strtolower(trim((string) $request->input('nivel_programa', '')), 'UTF-8'),
        ]);

        $validated = $request->validate([
            'nivel_programa' => [
                'required',
                'string',
                'max:120',
                'regex:/^[\p{L}\s]+$/u',
                Rule::unique('nivel_programa', 'nivel_programa')->ignore($nivel->id_nivel_programa, 'id_nivel_programa'),
            ],
            'meses' => 'required|integer|min:1|max:120',
        ], [
            'nivel_programa.required' => 'El nombre del nivel es obligatorio.',
            'nivel_programa.unique' => 'Ya existe un nivel con ese nombre.',
            'nivel_programa.regex' => 'El nombre solo puede contener letras y espacios.',
            'meses.required' => 'Indique los meses (se sincroniza la tabla duracion).',
            'meses.min' => 'La duración debe ser de al menos 1 mes.',
            'meses.max' => 'La duración no puede superar 120 meses.',
        ]);

        DB::transaction(function () use ($validated, $nivel): void {
            $idDur = Duracion::idForMeses((int) $validated['meses']);
            $nivel->update([
                'nivel_programa' => $validated['nivel_programa'],
            ]);
            if (Schema::hasColumn('nivel_programa', 'id_duracion')) {
                DB::table('nivel_programa')
                    ->where('id_nivel_programa', $nivel->id_nivel_programa)
                    ->update(['id_duracion' => $idDur]);
            }
        });

        return redirect()->route('niveles-programa.index')->with('success', 'Nivel actualizado correctamente.');
    }

    public function destroy($id)
    {
        $nivel = NivelPrograma::find($id);
        if (! $nivel) {
            return redirect()->route('niveles-programa.index')->with('error', 'Nivel no encontrado.');
        }

        if (Programa::where('id_nivel_programa', $nivel->id_nivel_programa)->exists()) {
            return redirect()->route('niveles-programa.index')->with('error', 'No se puede eliminar: hay programas que usan este nivel.');
        }

        $nivel->delete();

        return redirect()->route('niveles-programa.index')->with('success', 'Nivel eliminado correctamente.');
    }
}
