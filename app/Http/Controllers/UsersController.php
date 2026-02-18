<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SecurityAuditLog;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = DB::table('users')
            ->select(
                'id_cedula',
                'nombre',
                'apellido',
                'correo',
                'telefono',
                'user',
                'id_rol'
            );

        // Búsqueda por nombre, apellido, nombre completo, correo o usuario
        if ($request->filled('search')) {
            $search = \App\Helpers\SearchHelper::escapeLikeSpecialChars($request->search);
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%')
                  ->orWhere('apellido', 'like', '%' . $search . '%')
                  ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ['%' . $search . '%'])
                  ->orWhere('correo', 'like', '%' . $search . '%')
                  ->orWhere('user', 'like', '%' . $search . '%');
            });
        }

        $users = $query->orderBy('id_cedula', 'desc')->paginate(10);

        return view('users.index', [
            'users' => $users,
            'search' => $request->search ?? ''
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Cédula: solo números, máximo 10 dígitos
            'id_cedula' => [
                'required',
                'string',
                'regex:/^\d{1,10}$/',
                'unique:users,id_cedula',
            ],
            // Nombre: solo letras y espacios, máximo 20 caracteres
            'nombre' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\pL ]+$/u',
            ],
            // Apellido: solo letras y espacios, máximo 30 caracteres
            'apellido' => [
                'required',
                'string',
                'max:30',
                'regex:/^[\pL ]+$/u',
            ],
            // Correo: formato de email estándar
            'correo' => 'required|email|unique:users,correo|max:100',
            // Teléfono: solo números, máximo 10 dígitos (opcional)
            'telefono' => [
                'nullable',
                'string',
                'regex:/^\d{0,10}$/',
            ],
            // Usuario: solo letras y números, máximo 15 caracteres
            'user' => [
                'required',
                'string',
                'max:15',
                'regex:/^[A-Za-z0-9]+$/',
                'unique:users,user',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'confirmed',
            ],
            'id_rol' => 'required|integer|in:1,2,3',
        ], [
            'id_cedula.required' => 'El número de cédula es obligatorio.',
            'id_cedula.unique' => 'Esta cédula ya está registrada en el sistema.',
            'id_cedula.regex' => 'La cédula solo puede contener números y máximo 10 dígitos.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 20 caracteres.',
            'nombre.regex' => 'El nombre solo puede contener letras y un espacio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.max' => 'El apellido no puede tener más de 30 caracteres.',
            'apellido.regex' => 'El apellido solo puede contener letras y un espacio.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Debe ingresar un correo electrónico válido.',
            'correo.unique' => 'Este correo electrónico ya está registrado.',
            'telefono.regex' => 'El teléfono solo puede contener números y máximo 10 dígitos.',
            'user.required' => 'El nombre de usuario es obligatorio.',
            'user.max' => 'El usuario no puede tener más de 15 caracteres.',
            'user.regex' => 'El usuario solo puede contener letras y números (sin espacios).',
            'user.unique' => 'Este nombre de usuario ya está en uso. Por favor elige otro.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un símbolo especial (@$!%*?&).',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'id_rol.required' => 'Debe seleccionar un rol.',
            'id_rol.in' => 'El rol seleccionado no es válido.',
        ]);

        $nuevo = User::create([
            'id_cedula' => (string) $validated['id_cedula'],
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'correo' => $validated['correo'],
            'telefono' => $validated['telefono'] ?? null,
            'user' => $validated['user'],
            'password' => Hash::make($validated['password']),
            'id_rol' => $validated['id_rol'],
        ]);

        // Auditoría: creación de usuario
        try {
            $actor = Auth::user();
            SecurityAuditLog::create([
                'user_id'       => $actor?->id_cedula,
                'action'        => 'user_created',
                'resource_type' => 'user',
                'resource_id'   => $nuevo->id_cedula,
                'description'   => 'Creación de usuario con cédula ' . $nuevo->id_cedula,
                'ip_address'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // No interrumpir el flujo si falla el log
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }

        return view('users.edit', [
            'user' => $user
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
        }

        $validated = $request->validate([
            // Nombre: solo letras y espacios, máximo 20 caracteres
            'nombre' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\pL ]+$/u',
            ],
            // Apellido: solo letras y espacios, máximo 30 caracteres
            'apellido' => [
                'required',
                'string',
                'max:30',
                'regex:/^[\pL ]+$/u',
            ],
            // Correo: formato de email estándar
            'correo' => 'required|email|unique:users,correo,' . $user->id_cedula . ',id_cedula|max:100',
            // Teléfono: solo números, máximo 10 dígitos (opcional)
            'telefono' => [
                'nullable',
                'string',
                'regex:/^\d{0,10}$/',
            ],
            // Usuario: solo letras y números, máximo 15 caracteres
            'user' => [
                'required',
                'string',
                'max:15',
                'regex:/^[A-Za-z0-9]+$/',
                'unique:users,user,' . $user->id_cedula . ',id_cedula',
            ],
            'password_actual' => 'required_with:password|nullable|string',
            'password' => [
                'nullable',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'confirmed',
            ],
            'id_rol' => 'required|integer|in:1,2,3',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 20 caracteres.',
            'nombre.regex' => 'El nombre solo puede contener letras y un espacio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.max' => 'El apellido no puede tener más de 30 caracteres.',
            'apellido.regex' => 'El apellido solo puede contener letras y un espacio.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Debe ingresar un correo electrónico válido.',
            'correo.unique' => 'Este correo electrónico ya está registrado.',
            'telefono.regex' => 'El teléfono solo puede contener números y máximo 10 dígitos.',
            'user.required' => 'El nombre de usuario es obligatorio.',
            'user.max' => 'El usuario no puede tener más de 15 caracteres.',
            'user.regex' => 'El usuario solo puede contener letras y números (sin espacios).',
            'user.unique' => 'Este nombre de usuario ya está en uso. Por favor elige otro.',
            'password_actual.required_with' => 'Para cambiar la contraseña debes ingresar la contraseña actual del usuario.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un símbolo especial (@$!%*?&).',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'id_rol.required' => 'Debe seleccionar un rol.',
            'id_rol.in' => 'El rol seleccionado no es válido.',
        ]);

        // Si se va a cambiar la contraseña, verificar que la contraseña actual sea correcta
        if ($request->filled('password')) {
            if (!Hash::check($request->password_actual, $user->password)) {
                return redirect()
                    ->back()
                    ->withErrors(['password_actual' => 'La contraseña actual del usuario es incorrecta.'])
                    ->withInput();
            }
        }

        $user->nombre = $validated['nombre'];
        $user->apellido = $validated['apellido'];
        $user->correo = $validated['correo'];
        $user->telefono = $validated['telefono'] ?? null;
        $user->user = $validated['user'];
        $user->id_rol = $validated['id_rol'];

        // Actualizar contraseña solo si se proporciona
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Auditoría: actualización de usuario
        try {
            $actor = Auth::user();
            SecurityAuditLog::create([
                'user_id'       => $actor?->id_cedula,
                'action'        => 'user_updated',
                'resource_type' => 'user',
                'resource_id'   => $user->id_cedula,
                'description'   => 'Actualización de usuario con cédula ' . $user->id_cedula,
                'ip_address'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // No interrumpir el flujo si falla el log
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        // No permitir que un usuario se elimine a sí mismo
        if (auth()->user()->id_cedula === $id) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        try {
            $user = User::find($id);

            if (!$user) {
                return redirect()->route('users.index')->with('error', 'Usuario no encontrado.');
            }

            // Evitar eliminar al último administrador del sistema
            if ($user->id_rol === 1) {
                $totalAdmins = User::where('id_rol', 1)->count();
                if ($totalAdmins <= 1) {
                    return redirect()->route('users.index')->with('error', 'No puedes eliminar el último administrador del sistema.');
                }
            }

            $nombreCompleto = $user->nombre . ' ' . $user->apellido;
            $user->delete();

            // Auditoría: eliminación de usuario
            try {
                $actor = Auth::user();
                SecurityAuditLog::create([
                    'user_id'       => $actor?->id_cedula,
                    'action'        => 'user_deleted',
                    'resource_type' => 'user',
                    'resource_id'   => $id,
                    'description'   => 'Eliminación del usuario ' . $nombreCompleto . ' (cédula ' . $id . ')',
                    'ip_address'    => request()->ip(),
                    'user_agent'    => substr((string) request()->userAgent(), 0, 255),
                    'status'        => 'success',
                    'metadata'      => null,
                    'created_at'    => now(),
                ]);
            } catch (\Throwable $e) {
                // No interrumpir el flujo si falla el log
            }

            return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar usuario: ' . $e->getMessage());
            return redirect()->route('users.index')->with('error', 'Error al eliminar el usuario. Por favor, inténtalo de nuevo.');
        }
    }
}

