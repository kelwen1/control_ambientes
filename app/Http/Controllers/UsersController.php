<?php

namespace App\Http\Controllers;

use App\Helpers\EliminacionDependenciasHelper;
use App\Helpers\SearchHelper;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\SecurityAuditLog;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UsersController extends Controller
{
    /** Etiqueta legible del rol (tabla o ids conocidos). */
    private function etiquetaRol(int $idRol): string
    {
        return match ($idRol) {
            (int) config('roles.ids.administrador', 1) => 'Administrador',
            (int) config('roles.ids.coordinacion_L', 2) => 'Coordinador líder',
            (int) config('roles.ids.coordinacion', 3) => 'Coordinador',
            (int) config('roles.ids.instructor', 4) => 'Instructor',
            default => 'Otro',
        };
    }

    /**
     * Formulario de actualización de roles (instructor, coordinador, coordinador líder). Solo administrador.
     */
    public function showRoleUpdate()
    {
        $rolesCambio = [
            (int) config('roles.ids.coordinacion_L', 2) => 'Coordinador líder',
            (int) config('roles.ids.coordinacion', 3) => 'Coordinador',
            (int) config('roles.ids.instructor', 4) => 'Instructor',
        ];

        return view('users.role-update', [
            'rolesCambio' => $rolesCambio,
        ]);
    }

    /**
     * Valida cédula y devuelve datos del usuario (AJAX) para el formulario de cambio de rol.
     */
    public function lookupCedulaForRoleUpdate(Request $request)
    {
        $request->validate([
            'cedula' => ['required', 'string', 'regex:/^\d{1,10}$/'],
        ], [
            'cedula.required' => 'Ingrese la cédula.',
            'cedula.regex' => 'La cédula solo puede contener números (máximo 10 dígitos).',
        ]);

        $cedula = trim($request->query('cedula', ''));
        $user = User::with('persona.rol')
            ->where('id_persona', $cedula)
            ->first();

        if (! $user || ! $user->persona) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay ninguna cuenta de usuario con esa cédula.',
            ], 404);
        }

        $p = $user->persona;
        $idRol = (int) $p->id_rol;
        if ($idRol === (int) config('roles.ids.administrador', 1)) {
            $totalAdmins = User::whereHas('persona', fn ($q) => $q->where('id_rol', config('roles.ids.administrador', 1)))->count();
            if ($totalAdmins <= 1) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se puede reasignar al único administrador del sistema desde aquí. Cree otro administrador primero o use otro flujo.',
                ], 422);
            }
        }

        return response()->json([
            'ok' => true,
            'cedula' => (string) $p->id_persona,
            'nombres' => $p->nombres,
            'apellidos' => $p->apellidos,
            'nombre_completo' => trim($p->nombres.' '.$p->apellidos),
            'id_rol_actual' => $idRol,
            'rol_actual_etiqueta' => $this->etiquetaRol($idRol),
        ]);
    }

    /**
     * Aplica el nuevo rol a la persona (solo roles 2, 3, 4).
     */
    public function updateRole(Request $request)
    {
        $permitidos = [
            (int) config('roles.ids.coordinacion_L', 2),
            (int) config('roles.ids.coordinacion', 3),
            (int) config('roles.ids.instructor', 4),
        ];
        $validated = $request->validate([
            'cedula' => [
                'required',
                'string',
                'regex:/^\d{1,10}$/',
                'exists:users,id_persona',
            ],
            'id_rol' => 'required|integer|in:'.implode(',', $permitidos),
        ], [
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.regex' => 'La cédula solo puede contener números.',
            'cedula.exists' => 'No existe un usuario con esa cédula.',
            'id_rol.in' => 'Debe elegir un rol permitido: Instructor, Coordinador o Coordinador líder.',
        ]);

        $cedula = trim($validated['cedula']);
        $myCedula = (string) (Auth::user()->id_cedula ?? '');
        if ($myCedula !== '' && $cedula === $myCedula) {
            return redirect()
                ->route('users.role-update')
                ->with('error', 'No puede actualizar su propio rol en esta pantalla por seguridad.');
        }

        $user = User::where('id_persona', $cedula)->with('persona')->first();
        if (! $user?->persona) {
            return redirect()->route('users.role-update')->with('error', 'Usuario no encontrado.');
        }

        $persona = $user->persona;
        $nuevoIdRol = (int) $validated['id_rol'];
        $rolAnterior = (int) $persona->id_rol;

        if ($rolAnterior === (int) config('roles.ids.administrador', 1)) {
            $totalAdmins = User::whereHas('persona', fn ($q) => $q->where('id_rol', config('roles.ids.administrador', 1)))->count();
            if ($totalAdmins <= 1) {
                return redirect()
                    ->route('users.role-update')
                    ->with('error', 'No puede quitarse el rol al único administrador del sistema.');
            }
        }

        if ($rolAnterior === $nuevoIdRol) {
            return redirect()
                ->route('users.role-update')
                ->with('info', 'No hay cambios: el rol seleccionado es el que ya tenía el usuario.');
        }

        $persona->id_rol = $nuevoIdRol;
        $persona->save();

        try {
            SecurityAuditLog::create([
                'user_id' => (string) Auth::user()?->id_cedula,
                'action' => 'user_role_changed',
                'resource_type' => 'persona',
                'resource_id' => (string) $cedula,
                'description' => 'Cambio de rol cédula '.$cedula.': '.($this->etiquetaRol($rolAnterior)).' → '.($this->etiquetaRol($nuevoIdRol)),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'status' => 'success',
                'metadata' => null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // no interrumpir
        }

        $nombre = trim($persona->nombres.' '.$persona->apellidos);

        return redirect()
            ->route('users.role-update')
            ->with('success', 'Rol actualizado: '.$nombre.' ahora es '.$this->etiquetaRol($nuevoIdRol).'.');
    }

    /**
     * Listado de usuarios (persona + user + rol). Muestra quién creó a cada persona.
     */
    public function index(Request $request)
    {
        $query = User::with(['persona.rol', 'creator']);

        if ($request->filled('search')) {
            $search = SearchHelper::escapeLikeSpecialChars(trim($request->search));
            $query->whereHas('persona', function ($p) use ($search) {
                $p->where('persona.id_persona', 'like', '%'.$search.'%');
            });
        }

        $users = $query->orderBy('users.id_usuario', 'desc')->paginate(10)->withQueryString();

        return view('users.index', [
            'users' => $users,
            'search' => $request->search ?? '',
        ]);
    }

    /**
     * Formulario para crear nueva persona y su usuario (user/password = cédula).
     */
    public function create()
    {
        $roles = Rol::orderBy('id_rol')->get();

        return view('users.create', ['roles' => $roles]);
    }

    /**
     * Crear persona y user. user/password = cédula. created_by/updated_by = admin logueado.
     */
    public function store(Request $request)
    {
        $correoProveedorTld = '/^[\p{L}\p{N}._%+\-]+@(gmail|outlook|hotmail)\.(com|co|edu|sena)$/iu';

        $validated = $request->validate([
            'cedula' => [
                'required',
                'string',
                'regex:/^\d{1,10}$/',
                'unique:persona,id_persona',
            ],
            'nombres' => ['required', 'string', 'max:40', 'regex:/^[\p{L}\s]+$/u'],
            'apellidos' => ['required', 'string', 'max:50', 'regex:/^[\p{L}\s]+$/u'],
            'correo' => ['required', 'email', 'max:50', 'regex:'.$correoProveedorTld, 'unique:persona,correo'],
            'telefono' => ['nullable', 'string', 'regex:/^(\d{1,10})?$/'],
            'id_rol' => 'required|integer|exists:rol,id_rol',
        ], [
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.regex' => 'La cédula solo puede contener números (máximo 10 dígitos).',
            'cedula.unique' => 'Esta cédula ya está registrada.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'nombres.max' => 'Los nombres no pueden superar 40 caracteres.',
            'nombres.regex' => 'Los nombres solo pueden contener letras y espacios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.regex' => 'Use un correo @gmail, @outlook o @hotmail con terminación .com, .co, .edu o .sena.',
            'correo.unique' => 'Este correo ya está registrado.',
            'telefono.regex' => 'El teléfono solo puede contener números (máximo 10 dígitos).',
            'id_rol.required' => 'Debe seleccionar un rol.',
            'id_rol.exists' => 'El rol no es válido.',
        ]);

        $cedula = trim($validated['cedula']);
        $actorIdPersona = Auth::user()->persona->id_persona ?? null;

        // Persona es la tabla madre: no lleva created_by/updated_by.
        $persona = Persona::create([
            'id_persona' => $cedula,
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'correo' => $validated['correo'],
            'telefono' => $validated['telefono'] ?? null,
            'id_rol' => $validated['id_rol'],
        ]);

        $userData = [
            'id_persona' => $persona->id_persona,
            'user' => $cedula,
            'password' => $cedula,
        ];
        if (Schema::hasColumn('users', 'created_by') && $actorIdPersona !== null) {
            $userData['created_by'] = $actorIdPersona;
            $userData['updated_by'] = $actorIdPersona;
        }
        $user = User::create($userData);

        try {
            SecurityAuditLog::create([
                'user_id' => (string) Auth::user()?->id_cedula,
                'action' => 'user_created',
                'resource_type' => 'user',
                'resource_id' => (string) $cedula,
                'description' => 'Creación de usuario (persona) con cédula '.$cedula,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'status' => 'success',
                'metadata' => null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // no interrumpir
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente. Inicio de sesión: cédula / cédula.');
    }

    /**
     * Editar usuario (persona + cuenta). id = id_usuario.
     */
    public function edit($id)
    {
        $user = User::with('persona.rol')->find($id);
        if (! $user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }

        return view('users.edit', ['user' => $user]);
    }

    /**
     * Actualizar nombres, apellidos, teléfono y opcionalmente contraseña. Correo y rol no se modifican.
     */
    public function update(Request $request, $id)
    {
        $user = User::with('persona')->find($id);
        if (! $user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }

        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:40', 'regex:/^[\p{L}\s]+$/u'],
            'apellidos' => ['required', 'string', 'max:50', 'regex:/^[\p{L}\s]+$/u'],
            'telefono' => ['nullable', 'string', 'regex:/^(\d{1,10})?$/'],
            'contraseña_actual' => 'required_with:contraseña|nullable|string',
            'contraseña' => [
                'nullable',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'confirmed',
            ],
        ], [
            'nombres.required' => 'Los nombres son obligatorios.',
            'nombres.max' => 'Los nombres no pueden superar 40 caracteres.',
            'nombres.regex' => 'Los nombres solo pueden contener letras y espacios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            'telefono.regex' => 'El teléfono solo puede contener números (máximo 10 dígitos).',
            'contraseña_actual.required_with' => 'Para cambiar la contraseña debe ingresar la contraseña actual.',
            'contraseña.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
        ]);

        $actorIdPersona = Auth::user()->persona->id_persona ?? null;

        if ($request->filled('contraseña')) {
            if (! Hash::check($request->contraseña_actual, $user->contraseña)) {
                return back()->withErrors(['contraseña_actual' => 'La contraseña actual es incorrecta.'])->withInput();
            }
            $user->password = Hash::make($validated['contraseña']);
            if (Schema::hasColumn('users', 'updated_by') && $actorIdPersona !== null) {
                $user->updated_by = $actorIdPersona;
            }
            $user->save();
        }

        $persona = $user->persona;
        $persona->nombres = $validated['nombres'];
        $persona->apellidos = $validated['apellidos'];
        $persona->telefono = $validated['telefono'] ?? null;
        $persona->save();

        try {
            SecurityAuditLog::create([
                'user_id' => (string) Auth::user()?->id_cedula,
                'action' => 'user_updated',
                'resource_type' => 'user',
                'resource_id' => (string) $user->id_cedula,
                'description' => 'Actualización de usuario (persona) cédula '.$user->id_cedula,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'status' => 'success',
                'metadata' => null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // no interrumpir
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Eliminar usuario y su persona. id = id_usuario. No permitir eliminarse a sí mismo ni al último admin.
     */
    public function destroy($id)
    {
        if (Auth::id() == $id) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user = User::with('persona')->find($id);
        if (! $user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }

        if ((int) $user->persona->id_rol === config('roles.ids.administrador', 1)) {
            $totalAdmins = User::whereHas('persona', fn ($q) => $q->where('id_rol', config('roles.ids.administrador', 1)))->count();
            if ($totalAdmins <= 1) {
                return redirect()->route('users.index')->with('error', 'No puedes eliminar el último administrador.');
            }
        }

        $cedula = $user->persona->id_persona;
        $motivoReservas = EliminacionDependenciasHelper::motivoNoEliminarUsuarioPorReservas((string) $cedula);
        if ($motivoReservas !== null) {
            return redirect()->route('users.index')->with('error', $motivoReservas);
        }

        $motivoRefs = EliminacionDependenciasHelper::motivoNoEliminarPersonaPorReferencias((string) $cedula);
        if ($motivoRefs !== null) {
            return redirect()->route('users.index')->with('error', $motivoRefs);
        }

        $nombreCompleto = $user->persona->nombres.' '.$user->persona->apellidos;
        try {
            DB::transaction(function () use ($user, $cedula) {
                $user->delete();
                Persona::where('id_persona', $cedula)->delete();
            });
        } catch (QueryException $e) {
            $msg = $e->getMessage();
            if (($e->errorInfo[1] ?? null) === 1451 || str_contains($msg, '1451') || str_contains($msg, 'foreign key constraint')) {
                return redirect()->route('users.index')->with(
                    'error',
                    'No se puede eliminar este usuario porque la base de datos aún tiene registros vinculados a su persona (claves foráneas). Elimine o reasigne esas dependencias e intente de nuevo.'
                );
            }
            throw $e;
        }

        try {
            SecurityAuditLog::create([
                'user_id' => (string) Auth::user()?->id_cedula,
                'action' => 'user_deleted',
                'resource_type' => 'user',
                'resource_id' => (string) $cedula,
                'description' => 'Eliminación del usuario '.$nombreCompleto.' (cédula '.$cedula.')',
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'status' => 'success',
                'metadata' => null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // no interrumpir
        }

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
