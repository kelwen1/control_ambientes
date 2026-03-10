<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\SecurityAuditLog;
use App\Helpers\SearchHelper;

class UsersController extends Controller
{
    /**
     * Listado de usuarios (persona + user + rol). Muestra quién creó a cada persona.
     */
    public function index(Request $request)
    {
        $query = User::with(['persona.rol', 'creator']);

        if ($request->filled('search')) {
            $search = SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('persona', function ($p) use ($search) {
                    $p->where('persona.nombres', 'like', '%' . $search . '%')
                      ->orWhere('persona.apellidos', 'like', '%' . $search . '%')
                      ->orWhereRaw("CONCAT(persona.nombres, ' ', persona.apellidos) LIKE ?", ['%' . $search . '%'])
                      ->orWhere('persona.correo', 'like', '%' . $search . '%')
                      ->orWhere('persona.id_persona', 'like', '%' . $search . '%');
                })->orWhere('users.user', 'like', '%' . $search . '%');
            });
        }

        $users = $query->orderBy('users.id_usuario', 'desc')->paginate(10);

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
        $validated = $request->validate([
            'cedula' => [
                'required',
                'string',
                'max:20',
                'unique:persona,id_persona',
            ],
            'nombres' => ['required', 'string', 'max:50', 'regex:/^[\pL\s]+$/u'],
            'apellidos' => ['required', 'string', 'max:50', 'regex:/^[\pL\s]+$/u'],
            'correo' => 'required|email|max:50|unique:persona,correo',
            'telefono' => 'nullable|string|max:10',
            'id_rol' => 'required|integer|exists:rol,id_rol',
        ], [
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.unique' => 'Esta cédula ya está registrada.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.unique' => 'Este correo ya está registrado.',
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
                'user_id'       => (string) Auth::user()?->id_cedula,
                'action'        => 'user_created',
                'resource_type' => 'user',
                'resource_id'   => (string) $cedula,
                'description'   => 'Creación de usuario (persona) con cédula ' . $cedula,
                'ip_address'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
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
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }
        $roles = Rol::orderBy('id_rol')->get();
        return view('users.edit', ['user' => $user, 'roles' => $roles]);
    }

    /**
     * Actualizar persona y opcionalmente contraseña del user. updated_by = admin logueado.
     */
    public function update(Request $request, $id)
    {
        $user = User::with('persona')->find($id);
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }

        $validated = $request->validate([
            'nombres'   => ['required', 'string', 'max:50', 'regex:/^[\pL\s]+$/u'],
            'apellidos' => ['required', 'string', 'max:50', 'regex:/^[\pL\s]+$/u'],
            'correo'    => 'required|email|max:50|unique:persona,correo,' . $user->persona->id_persona . ',id_persona',
            'telefono'  => 'nullable|string|max:10',
            'id_rol'    => 'required|integer|exists:rol,id_rol',
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
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'correo.unique' => 'Este correo ya está registrado.',
            'id_rol.exists' => 'El rol no es válido.',
            'contraseña_actual.required_with' => 'Para cambiar la contraseña debe ingresar la contraseña actual.',
            'contraseña.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
        ]);

        $actorIdPersona = Auth::user()->persona->id_persona ?? null;

        if ($request->filled('contraseña')) {
            if (!Hash::check($request->contraseña_actual, $user->contraseña)) {
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
        $persona->correo = $validated['correo'];
        $persona->telefono = $validated['telefono'] ?? null;
        $persona->id_rol = $validated['id_rol'];
        $persona->save();

        try {
            SecurityAuditLog::create([
                'user_id'       => (string) Auth::user()?->id_cedula,
                'action'        => 'user_updated',
                'resource_type' => 'user',
                'resource_id'   => (string) $user->id_cedula,
                'description'   => 'Actualización de usuario (persona) cédula ' . $user->id_cedula,
                'ip_address'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
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
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }

        if ((int) $user->persona->id_rol === config('roles.ids.administrador', 1)) {
            $totalAdmins = User::whereHas('persona', fn ($q) => $q->where('id_rol', config('roles.ids.administrador', 1)))->count();
            if ($totalAdmins <= 1) {
                return redirect()->route('users.index')->with('error', 'No puedes eliminar el último administrador.');
            }
        }

        $nombreCompleto = $user->persona->nombres . ' ' . $user->persona->apellidos;
        $cedula = $user->persona->id_persona;

        $user->delete();
        Persona::where('id_persona', $cedula)->delete();

        try {
            SecurityAuditLog::create([
                'user_id'       => (string) Auth::user()?->id_cedula,
                'action'        => 'user_deleted',
                'resource_type' => 'user',
                'resource_id'   => (string) $cedula,
                'description'   => 'Eliminación del usuario ' . $nombreCompleto . ' (cédula ' . $cedula . ')',
                'ip_address'    => request()->ip(),
                'user_agent'    => substr((string) request()->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // no interrumpir
        }

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
