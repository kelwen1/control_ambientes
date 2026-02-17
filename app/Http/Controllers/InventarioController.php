<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Ambiente;
use App\Models\SecurityAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InventarioController extends Controller
{
    /**
     * Display a listing of the inventory.
     */
    public function index(Request $request)
    {
        // Obtener los registros de inventario con información del ambiente (paginado a 10 por página)
        $query = DB::table('inventario')
            ->leftJoin('ambientes', 'inventario.id_ambiente', '=', 'ambientes.id_ambiente')
            ->select(
                'inventario.id_Inventario',
                'inventario.id_ambiente',
                'inventario.computadores',
                'inventario.sillas',
                'inventario.mesas',
                'inventario.aire_acondicionado',
                'inventario.tablero',
                'inventario.televisor',
                'inventario.ventiladores',
                'inventario.vidiovid',
                'inventario.herramientas',
                'ambientes.num_ambiente'
            );

        // Búsqueda avanzada por múltiples criterios
        if ($request->filled('search')) {
            $search = \App\Helpers\SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where(function($q) use ($search) {
                $q->where('ambientes.num_ambiente', 'like', '%' . $search . '%')
                  ->orWhere('inventario.computadores', 'like', '%' . $search . '%')
                  ->orWhere('inventario.sillas', 'like', '%' . $search . '%')
                  ->orWhere('inventario.mesas', 'like', '%' . $search . '%');
            });
        }

        $inventarios = $query->orderBy('inventario.id_Inventario', 'desc')->paginate(10);

        // Cantidades (computadores, sillas, mesas, aire) como entero; el resto Sí/No
        $inventarios->getCollection()->transform(function ($item) {
            foreach (['computadores', 'sillas', 'mesas', 'aire_acondicionado'] as $campo) {
                $item->$campo = (int) ($item->$campo ?? 0);
            }
            foreach (['tablero', 'televisor', 'ventiladores', 'vidiovid', 'herramientas'] as $campo) {
                $v = $item->$campo ?? 0;
                $item->$campo = ($v === 1 || $v === '1') ? 'Sí' : 'No';
            }
            return $item;
        });

        return view('inventario.index', [
            'inventarios' => $inventarios,
            'search' => $request->search ?? ''
        ]);
    }

    /**
     * Export inventario a PDF
     */
    public function export(Request $request)
    {
        $query = DB::table('inventario')
            ->leftJoin('ambientes', 'inventario.id_ambiente', '=', 'ambientes.id_ambiente')
            ->select(                
                'inventario.id_ambiente',
                'inventario.computadores',
                'inventario.sillas',
                'inventario.mesas',
                'inventario.aire_acondicionado',
                'inventario.tablero',
                'inventario.televisor',
                'inventario.ventiladores',
                'inventario.vidiovid',
                'inventario.herramientas',
                'ambientes.num_ambiente'
            );

        if ($request->filled('search')) {
            $search = \App\Helpers\SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where(function($q) use ($search) {
                $q->where('ambientes.num_ambiente', 'like', '%' . $search . '%')
                  ->orWhere('inventario.computadores', 'like', '%' . $search . '%')
                  ->orWhere('inventario.sillas', 'like', '%' . $search . '%')
                  ->orWhere('inventario.mesas', 'like', '%' . $search . '%');
            });
        }

        $inventarios = $query->orderBy('inventario.id_Inventario', 'desc')->get();
        $filename = 'inventario_' . date('Y-m-d_His') . '.pdf';
        return app('dompdf.wrapper')->loadView('pdf.inventario', compact('inventarios'))
            ->download($filename);
    }

    /**
     * Show the form for creating a new inventory record.
     */
    public function create()
    {
        // Obtener todos los ambientes para el select
        $ambientes = DB::table('ambientes')
            ->select('id_ambiente', 'num_ambiente')
            ->orderByRaw('CAST(num_ambiente AS UNSIGNED), num_ambiente')
            ->get();

        // Crear array de opciones para el select (1-36, 17A, 17B)
        $opcionesAmbientes = [];
        for ($i = 1; $i <= 36; $i++) {
            if ($i == 17) {
                $opcionesAmbientes[] = ['value' => '17A', 'label' => '17A'];
                $opcionesAmbientes[] = ['value' => '17B', 'label' => '17B'];
            } else {
                $opcionesAmbientes[] = ['value' => (string)$i, 'label' => (string)$i];
            }
        }

        return view('inventario.create', [
            'ambientes' => $ambientes,
            'opcionesAmbientes' => $opcionesAmbientes
        ]);
    }

    /**
     * Store a newly created inventory record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'num_ambiente' => 'required|string',
            'computadores' => 'required|integer|min:0|max:35',
            'sillas' => 'required|integer|min:0|max:40',
            'mesas' => 'required|integer|min:0|max:20',
            'aire_acondicionado' => 'required|integer|min:0|max:9',
            'tablero' => 'required|string|in:Sí,No',
            'televisor' => 'required|string|in:Sí,No',
            'ventiladores' => 'required|string|in:Sí,No',
            'vidiovid' => 'required|string|in:Sí,No',
            'herramientas' => 'required|string|in:Sí,No',
        ], [
            'num_ambiente.required' => 'El ambiente es obligatorio.',
            'computadores.required' => 'Indique la cantidad de computadores (0-35).',
            'computadores.integer' => 'Solo números.',
            'computadores.min' => 'Mínimo 0.',
            'computadores.max' => 'Máximo 35.',
            'sillas.required' => 'Indique la cantidad de sillas (0-40).',
            'sillas.integer' => 'Solo números.',
            'sillas.min' => 'Mínimo 0.',
            'sillas.max' => 'Máximo 40.',
            'mesas.required' => 'Indique la cantidad de mesas (0-20).',
            'mesas.integer' => 'Solo números.',
            'mesas.min' => 'Mínimo 0.',
            'mesas.max' => 'Máximo 20.',
            'aire_acondicionado.required' => 'Indique la cantidad de aires (0-9).',
            'aire_acondicionado.integer' => 'Solo números.',
            'aire_acondicionado.min' => 'Mínimo 0.',
            'aire_acondicionado.max' => 'Máximo 9.',
            'tablero.required' => 'Debe seleccionar si hay tablero.',
            'tablero.in' => 'El valor debe ser Sí o No.',
            'televisor.required' => 'Debe seleccionar si hay televisor.',
            'televisor.in' => 'El valor debe ser Sí o No.',
            'ventiladores.required' => 'Debe seleccionar si hay ventiladores.',
            'ventiladores.in' => 'El valor debe ser Sí o No.',
            'vidiovid.required' => 'Debe seleccionar si hay videobeam.',
            'vidiovid.in' => 'El valor debe ser Sí o No.',
            'herramientas.required' => 'Debe seleccionar si hay herramientas.',
            'herramientas.in' => 'El valor debe ser Sí o No.',
        ]);

        // Buscar el id_ambiente basado en el num_ambiente seleccionado
        $ambiente = DB::table('ambientes')
            ->where('num_ambiente', $request->num_ambiente)
            ->first();

        if (!$ambiente) {
            return back()->withErrors(['num_ambiente' => 'El ambiente seleccionado no existe.'])->withInput();
        }

        // Verificar si ya existe un inventario para este ambiente
        $inventarioExistente = DB::table('inventario')
            ->where('id_ambiente', $ambiente->id_ambiente)
            ->exists();

        if ($inventarioExistente) {
            return back()
                ->withErrors(['num_ambiente' => 'Ya existe un inventario registrado para este ambiente. Por favor, edita el inventario existente en lugar de crear uno nuevo.'])
                ->withInput();
        }

        $convertirSiNo = function ($v) {
            return ($v === 'Sí' || $v === '1') ? 1 : 0;
        };

        // Crear el registro de inventario
        $inventario = Inventario::create([
            'id_ambiente' => $ambiente->id_ambiente,
            'computadores' => (int) $request->computadores,
            'sillas' => (int) $request->sillas,
            'mesas' => (int) $request->mesas,
            'aire_acondicionado' => (int) $request->aire_acondicionado,
            'tablero' => $convertirSiNo($request->tablero),
            'televisor' => $convertirSiNo($request->televisor),
            'ventiladores' => $convertirSiNo($request->ventiladores),
            'vidiovid' => $convertirSiNo($request->vidiovid),
            'herramientas' => $convertirSiNo($request->herramientas),
        ]);

        // Registrar auditoría de creación de inventario
        try {
            $user = Auth::user();
            SecurityAuditLog::create([
                'user_id'       => $user?->id_cedula,
                'action'        => 'inventario_created',
                'resource_type' => 'inventario',
                'resource_id'   => $inventario->id_Inventario ?? null,
                'description'   => 'Creación de inventario para ambiente ID ' . $ambiente->id_ambiente,
                'ip_address'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // No romper el flujo si falla el log
        }

        return redirect()->route('inventario.index')->with('success', 'Inventario asociado al ambiente correctamente.');
    }

    /**
     * Show the form for editing the specified inventory record.
     */
    public function edit($id)
    {
        $inventario = DB::table('inventario')
            ->leftJoin('ambientes', 'inventario.id_ambiente', '=', 'ambientes.id_ambiente')
            ->where('inventario.id_Inventario', $id)
            ->select(
                'inventario.id_Inventario',
                'inventario.id_ambiente',
                'inventario.computadores',
                'inventario.sillas',
                'inventario.mesas',
                'inventario.aire_acondicionado',
                'inventario.tablero',
                'inventario.televisor',
                'inventario.ventiladores',
                'inventario.vidiovid',
                'inventario.herramientas',
                'ambientes.num_ambiente'
            )
            ->first();

        if (!$inventario) {
            return redirect()->route('inventario.index')->with('error', 'Registro de inventario no encontrado.');
        }

        // Cantidades como entero; el resto Sí/No para el formulario
        foreach (['computadores', 'sillas', 'mesas', 'aire_acondicionado'] as $c) {
            $inventario->$c = (int) ($inventario->$c ?? 0);
        }
        foreach (['tablero', 'televisor', 'ventiladores', 'vidiovid', 'herramientas'] as $c) {
            $v = $inventario->$c ?? 0;
            $inventario->$c = ($v === 1 || $v === '1') ? 'Sí' : 'No';
        }

        // Crear array de opciones para el select (1-36, 17A, 17B)
        $opcionesAmbientes = [];
        for ($i = 1; $i <= 36; $i++) {
            if ($i == 17) {
                $opcionesAmbientes[] = ['value' => '17A', 'label' => '17A'];
                $opcionesAmbientes[] = ['value' => '17B', 'label' => '17B'];
            } else {
                $opcionesAmbientes[] = ['value' => (string)$i, 'label' => (string)$i];
            }
        }

        return view('inventario.edit', [
            'inventario' => $inventario,
            'opcionesAmbientes' => $opcionesAmbientes
        ]);
    }

    /**
     * Update the specified inventory record.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'num_ambiente' => 'required|string',
            'computadores' => 'required|integer|min:0|max:35',
            'sillas' => 'required|integer|min:0|max:40',
            'mesas' => 'required|integer|min:0|max:20',
            'aire_acondicionado' => 'required|integer|min:0|max:9',
            'tablero' => 'required|string|in:Sí,No',
            'televisor' => 'required|string|in:Sí,No',
            'ventiladores' => 'required|string|in:Sí,No',
            'vidiovid' => 'required|string|in:Sí,No',
            'herramientas' => 'required|string|in:Sí,No',
        ], [
            'num_ambiente.required' => 'El ambiente es obligatorio.',
            'computadores.required' => 'Indique la cantidad de computadores (0-35).',
            'computadores.integer' => 'Solo números.',
            'computadores.min' => 'Mínimo 0.',
            'computadores.max' => 'Máximo 35.',
            'sillas.required' => 'Indique la cantidad de sillas (0-40).',
            'sillas.integer' => 'Solo números.',
            'sillas.min' => 'Mínimo 0.',
            'sillas.max' => 'Máximo 40.',
            'mesas.required' => 'Indique la cantidad de mesas (0-20).',
            'mesas.integer' => 'Solo números.',
            'mesas.min' => 'Mínimo 0.',
            'mesas.max' => 'Máximo 20.',
            'aire_acondicionado.required' => 'Indique la cantidad de aires (0-9).',
            'aire_acondicionado.integer' => 'Solo números.',
            'aire_acondicionado.min' => 'Mínimo 0.',
            'aire_acondicionado.max' => 'Máximo 9.',
            'tablero.required' => 'Debe seleccionar si hay tablero.',
            'tablero.in' => 'El valor debe ser Sí o No.',
            'televisor.required' => 'Debe seleccionar si hay televisor.',
            'televisor.in' => 'El valor debe ser Sí o No.',
            'ventiladores.required' => 'Debe seleccionar si hay ventiladores.',
            'ventiladores.in' => 'El valor debe ser Sí o No.',
            'vidiovid.required' => 'Debe seleccionar si hay videobeam.',
            'vidiovid.in' => 'El valor debe ser Sí o No.',
            'herramientas.required' => 'Debe seleccionar si hay herramientas.',
            'herramientas.in' => 'El valor debe ser Sí o No.',
        ]);

        // Buscar el id_ambiente basado en el num_ambiente seleccionado
        $ambiente = DB::table('ambientes')
            ->where('num_ambiente', $request->num_ambiente)
            ->first();

        if (!$ambiente) {
            return back()->withErrors(['num_ambiente' => 'El ambiente seleccionado no existe.'])->withInput();
        }

        // Verificar si ya existe otro inventario para este ambiente (excluyendo el actual)
        $inventarioExistente = DB::table('inventario')
            ->where('id_ambiente', $ambiente->id_ambiente)
            ->where('id_Inventario', '!=', $id)
            ->exists();

        if ($inventarioExistente) {
            return back()
                ->withErrors(['num_ambiente' => 'Ya existe otro inventario registrado para este ambiente. Por favor, elige otro ambiente o edita el inventario existente.'])
                ->withInput();
        }

        $convertirSiNo = function ($v) {
            return ($v === 'Sí' || $v === '1') ? 1 : 0;
        };

        // Actualizar el registro de inventario
        Inventario::where('id_Inventario', $id)->update([
            'id_ambiente' => $ambiente->id_ambiente,
            'computadores' => (int) $request->computadores,
            'sillas' => (int) $request->sillas,
            'mesas' => (int) $request->mesas,
            'aire_acondicionado' => (int) $request->aire_acondicionado,
            'tablero' => $convertirSiNo($request->tablero),
            'televisor' => $convertirSiNo($request->televisor),
            'ventiladores' => $convertirSiNo($request->ventiladores),
            'vidiovid' => $convertirSiNo($request->vidiovid),
            'herramientas' => $convertirSiNo($request->herramientas),
        ]);

        // Registrar auditoría de actualización de inventario
        try {
            $user = Auth::user();
            SecurityAuditLog::create([
                'user_id'       => $user?->id_cedula,
                'action'        => 'inventario_updated',
                'resource_type' => 'inventario',
                'resource_id'   => (int) $id,
                'description'   => 'Actualización de inventario ID ' . $id,
                'ip_address'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // No romper el flujo si falla el log
        }

        return redirect()->route('inventario.index')->with('success', 'Inventario actualizado correctamente.');
    }

    /**
     * Remove the specified inventory record.
     */
    public function destroy($id)
    {
        try {
            // Convertir el ID a entero por si viene como string
            $id = (int) $id;
            
            // Verificar que el registro existe
            $inventario = DB::table('inventario')
                ->where('id_Inventario', $id)
                ->first();

            if (!$inventario) {
                return redirect()->route('inventario.index')->with('error', 'Registro de inventario no encontrado (ID: ' . $id . ').');
            }

            // Eliminar directamente con DB
            $deleted = DB::table('inventario')
                ->where('id_Inventario', $id)
                ->delete();

            if ($deleted > 0) {
                // Registrar auditoría de eliminación de inventario
                try {
                    $user = Auth::user();
                    SecurityAuditLog::create([
                        'user_id'       => $user?->id_cedula,
                        'action'        => 'inventario_deleted',
                        'resource_type' => 'inventario',
                        'resource_id'   => $id,
                        'description'   => 'Eliminación de inventario ID ' . $id,
                        'ip_address'    => $request->ip(),
                        'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                        'status'        => 'success',
                        'metadata'      => null,
                        'created_at'    => now(),
                    ]);
                } catch (\Throwable $e) {
                    // No romper el flujo si falla el log
                }

                return redirect()->route('inventario.index')->with('success', 'Inventario eliminado correctamente.');
            } else {
                return redirect()->route('inventario.index')->with('error', 'No se pudo eliminar el inventario. Inténtalo de nuevo.');
            }
        } catch (\Exception $e) {
            Log::error('Error al eliminar inventario: ' . $e->getMessage());
            return redirect()->route('inventario.index')->with('error', 'Error al eliminar el inventario. Por favor, inténtalo de nuevo.');
        }
    }
}

