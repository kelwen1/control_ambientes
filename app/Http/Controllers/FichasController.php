<?php

namespace App\Http\Controllers;

use App\Models\Ficha;
use App\Models\Programa;
use App\Models\SecurityAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FichasController extends Controller
{
    /**
     * Display a listing of the fichas.
     */
    public function index(Request $request)
    {
        $query = Ficha::with('programa')
            ->select('ficha.*');

        // Búsqueda avanzada por múltiples criterios
        if ($request->filled('search')) {
            $search = \App\Helpers\SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where(function($q) use ($search) {
                $q->where('num_ficha', 'like', '%' . $search . '%')
                  ->orWhere('cant_aprendices', 'like', '%' . $search . '%')
                  ->orWhere('fecha_inicio', 'like', '%' . $search . '%')
                  ->orWhere('fecha_fin', 'like', '%' . $search . '%')
                  ->orWhereHas('programa', function($programaQuery) use ($search) {
                      $programaQuery->where('nombre_programa', 'like', '%' . $search . '%');
                  });
            });
        }

        $fichas = $query->orderBy('id_ficha', 'desc')->paginate(10);

        return view('fichas.index', [
            'fichas' => $fichas,
            'search' => $request->search ?? ''
        ]);
    }

    /**
     * Export fichas a PDF
     */
    public function export(Request $request)
    {
        $query = Ficha::with('programa');

        if ($request->filled('search')) {
            $search = \App\Helpers\SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where(function($q) use ($search) {
                $q->where('num_ficha', 'like', '%' . $search . '%')
                  ->orWhere('cant_aprendices', 'like', '%' . $search . '%')
                  ->orWhereHas('programa', function($programaQuery) use ($search) {
                      $programaQuery->where('nombre_programa', 'like', '%' . $search . '%');
                  });
            });
        }

        $fichas = $query->orderBy('id_ficha', 'desc')->get();
        $filename = 'fichas_' . date('Y-m-d_His') . '.pdf';
        return app('dompdf.wrapper')->loadView('pdf.fichas', compact('fichas'))
            ->download($filename);
    }

    /**
     * Show the form for creating a new ficha.
     */
    public function create()
    {
        // Obtener todos los programas para el select
        $programas = Programa::select('id_programa', 'nombre_programa')
            ->orderBy('nombre_programa')
            ->get();

        return view('fichas.create', [
            'programas' => $programas
        ]);
    }

    /**
     * Store a newly created ficha in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            // Máximo 9 dígitos para el número de ficha
            'num_ficha' => 'required|integer|min:1|max:999999999',
            'cant_aprendices' => 'required|integer|min:1|max:40',
            'id_programa' => 'required|integer|exists:programa,id_programa',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'fecha_productiva' => 'nullable|date|after_or_equal:fecha_inicio|before_or_equal:fecha_fin',
        ], [
            'num_ficha.required' => 'El número de ficha es obligatorio.',
            'num_ficha.integer' => 'El número de ficha debe ser un número entero.',
            'num_ficha.min' => 'El número de ficha debe ser mayor a 0.',
            'num_ficha.max' => 'El número de ficha no puede tener más de 9 dígitos.',
            'cant_aprendices.required' => 'La cantidad de aprendices es obligatoria.',
            'cant_aprendices.integer' => 'La cantidad de aprendices debe ser un número entero.',
            'cant_aprendices.min' => 'La cantidad de aprendices debe ser al menos 1.',
            'cant_aprendices.max' => 'La cantidad de aprendices no puede ser mayor a 40.',
            'id_programa.required' => 'El programa es obligatorio.',
            'id_programa.exists' => 'El programa seleccionado no es válido.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'fecha_productiva.after_or_equal' => 'La fecha productiva debe ser igual o posterior a la fecha de inicio.',
            'fecha_productiva.before_or_equal' => 'La fecha productiva debe ser igual o anterior a la fecha de fin.',
        ]);

        Ficha::create([
            'num_ficha' => $request->num_ficha,
            'cant_aprendices' => $request->cant_aprendices,
            'id_programa' => $request->id_programa,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'fecha_productiva' => $request->fecha_productiva ?? null,
        ]);

        // Auditoría: creación de ficha
        try {
            $user = Auth::user();
            $ultimaFicha = Ficha::latest('id_ficha')->first();
            SecurityAuditLog::create([
                'user_id'       => $user?->id_cedula,
                'action'        => 'ficha_created',
                'resource_type' => 'ficha',
                'resource_id'   => $ultimaFicha?->id_ficha,
                'description'   => 'Creación de ficha número ' . $request->num_ficha,
                'ip_address'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
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
        $ficha = Ficha::with('programa')->find($id);

        if (!$ficha) {
            return redirect()->route('fichas.index')->with('error', 'Ficha no encontrada.');
        }

        // Obtener todos los programas para el select
        $programas = Programa::select('id_programa', 'nombre_programa')
            ->orderBy('nombre_programa')
            ->get();

        return view('fichas.edit', [
            'ficha' => $ficha,
            'programas' => $programas
        ]);
    }

    /**
     * Update the specified ficha in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            // Máximo 9 dígitos para el número de ficha
            'num_ficha' => 'required|integer|min:1|max:999999999',
            'cant_aprendices' => 'required|integer|min:1|max:40',
            'id_programa' => 'required|integer|exists:programa,id_programa',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'fecha_productiva' => 'nullable|date|after_or_equal:fecha_inicio|before_or_equal:fecha_fin',
        ], [
            'num_ficha.required' => 'El número de ficha es obligatorio.',
            'num_ficha.integer' => 'El número de ficha debe ser un número entero.',
            'num_ficha.min' => 'El número de ficha debe ser mayor a 0.',
            'num_ficha.max' => 'El número de ficha no puede tener más de 9 dígitos.',
            'cant_aprendices.required' => 'La cantidad de aprendices es obligatoria.',
            'cant_aprendices.integer' => 'La cantidad de aprendices debe ser un número entero.',
            'cant_aprendices.min' => 'La cantidad de aprendices debe ser al menos 1.',
            'cant_aprendices.max' => 'La cantidad de aprendices no puede ser mayor a 40.',
            'id_programa.required' => 'El programa es obligatorio.',
            'id_programa.exists' => 'El programa seleccionado no es válido.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'fecha_productiva.after_or_equal' => 'La fecha productiva debe ser igual o posterior a la fecha de inicio.',
            'fecha_productiva.before_or_equal' => 'La fecha productiva debe ser igual o anterior a la fecha de fin.',
        ]);

        $ficha = Ficha::findOrFail($id);
        $ficha->update([
            'num_ficha' => $request->num_ficha,
            'cant_aprendices' => $request->cant_aprendices,
            'id_programa' => $request->id_programa,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'fecha_productiva' => $request->fecha_productiva ?? null,
        ]);

        // Auditoría: actualización de ficha
        try {
            $user = Auth::user();
            SecurityAuditLog::create([
                'user_id'       => $user?->id_cedula,
                'action'        => 'ficha_updated',
                'resource_type' => 'ficha',
                'resource_id'   => $ficha->id_ficha,
                'description'   => 'Actualización de ficha número ' . $request->num_ficha . ' (ID ' . $ficha->id_ficha . ')',
                'ip_address'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
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

            if (!$ficha) {
                return redirect()->route('fichas.index')->with('error', 'Ficha no encontrada.');
            }

            $numFicha = $ficha->num_ficha;
            $ficha->delete();

            // Auditoría: eliminación de ficha
            try {
                $user = Auth::user();
                SecurityAuditLog::create([
                    'user_id'       => $user?->id_cedula,
                    'action'        => 'ficha_deleted',
                    'resource_type' => 'ficha',
                    'resource_id'   => $id,
                    'description'   => 'Eliminación de ficha número ' . $numFicha . ' (ID ' . $id . ')',
                    'ip_address'    => request()->ip(),
                    'user_agent'    => substr((string) request()->userAgent(), 0, 255),
                    'status'        => 'success',
                    'metadata'      => null,
                    'created_at'    => now(),
                ]);
            } catch (\Throwable $e) {
                // No interrumpir el flujo si falla el log
            }

            return redirect()->route('fichas.index')->with('success', 'Ficha eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar ficha: ' . $e->getMessage());
            return redirect()->route('fichas.index')->with('error', 'Error al eliminar la ficha. Por favor, inténtalo de nuevo.');
        }
    }
}

