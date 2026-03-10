<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\SecurityAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(Request $request)
    {
        $loginAttempts = $request->session()->get('login_attempts', 0);
        $lockoutTime = $request->session()->get('lockout_time', null);
        
        // Verificar si el bloqueo ha expirado (2 minutos = 120 segundos)
        if ($lockoutTime && (time() - $lockoutTime) >= 120) {
            // Resetear contador y tiempo de bloqueo
            $request->session()->forget('login_attempts');
            $request->session()->forget('lockout_time');
            $loginAttempts = 0;
        }
        
        $isLocked = $loginAttempts >= 3 && $lockoutTime && (time() - $lockoutTime) < 120;
        $remainingLockoutSeconds = $isLocked ? 120 - (time() - $lockoutTime) : 0;
        
        // Calcular intentos restantes basado en el contador actual
        $remainingAttempts = max(0, 3 - $loginAttempts);
        
        return view('auth.login', [
            'loginAttempts' => $loginAttempts,
            'isLocked' => $isLocked,
            'remainingLockoutSeconds' => $remainingLockoutSeconds,
            'remainingAttempts' => $remainingAttempts
        ]);
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $loginAttempts = $request->session()->get('login_attempts', 0);
        $lockoutTime = $request->session()->get('lockout_time', null);
        
        // Verificar si el bloqueo ha expirado
        if ($lockoutTime && (time() - $lockoutTime) >= 120) {
            // Resetear contador y tiempo de bloqueo
            $request->session()->forget('login_attempts');
            $request->session()->forget('lockout_time');
            $loginAttempts = 0;
        }
        
        // Verificar si está bloqueado
        if ($loginAttempts >= 3 && $lockoutTime && (time() - $lockoutTime) < 120) {
            return back()->withInput();
        }

        $request->validate([
            'user' => 'required|string|max:255',
            'contraseña' => 'required|string|max:255',
        ]);

        // Sanitizar inputs
        $username = trim($request->input('user'));
        $password = $request->input('contraseña');

        // Buscar usuario
        $user = User::where('user', $username)->first();

        // Mensaje genérico para no revelar si el usuario existe
        if (!$user || !Hash::check($password, $user->contraseña)) {
            // Incrementar contador de intentos fallidos
            $newAttempts = $loginAttempts + 1;
            $request->session()->put('login_attempts', $newAttempts);
            
            // Si alcanza 3 intentos, guardar el tiempo de bloqueo
            if ($newAttempts >= 3) {
                $request->session()->put('lockout_time', time());
            }
            
            $remainingAttempts = max(0, 3 - $newAttempts);
            
            // Guardar información para el modal
            return back()->withErrors([
                'user' => ['Las credenciales proporcionadas son incorrectas.'],
            ])->with('show_modal', true)
              ->with('remaining_attempts', $remainingAttempts)
              ->withInput();
        }

        // Login exitoso: limpiar contador de intentos y tiempo de bloqueo
        $request->session()->forget('login_attempts');
        $request->session()->forget('lockout_time');
        $request->session()->forget('show_modal');
        $request->session()->forget('remaining_attempts');
        
        Auth::login($user);

        // Registrar log de login exitoso
        try {
            SecurityAuditLog::create([
                'user_id'       => $user->id_cedula,
                'action'        => 'login_success',
                'resource_type' => 'user',
                'resource_id'   => $user->id_cedula,
                'description'   => 'Login exitoso para usuario: ' . $user->user,
                'ip_address'    => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'status'        => 'success',
                'metadata'      => null,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // No interrumpir el flujo de autenticación si falla el log
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        $roles = Rol::orderBy('id_rol')->get();
        return view('auth.register', ['roles' => $roles]);
    }

    /**
     * Handle registration request.
     * Crea persona y user: user/contraseña = cedula (primer usuario: created_by/updated_by null).
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            // La cédula se almacena en la columna id_persona de la tabla persona
            'cedula' => 'required|string|max:20|unique:persona,id_persona',
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'correo' => 'required|email|max:50|unique:persona,correo',
            'telefono' => 'nullable|string|max:10',
            'id_rol' => 'required|integer|exists:rol,id_rol',
        ], [
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.unique' => 'Esta cédula ya está registrada.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'Correo electrónico válido.',
            'correo.unique' => 'Este correo ya está registrado.',
            'id_rol.required' => 'Debe seleccionar un rol.',
            'id_rol.exists' => 'El rol seleccionado no es válido.',
        ]);

        $cedula = trim($validated['cedula']);

        $persona = Persona::create([
            'id_persona' => $cedula,
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'correo' => $validated['correo'],
            'telefono' => $validated['telefono'] ?? null,
            'id_rol' => $validated['id_rol'],
        ]);

        $user = User::create([
            'id_persona' => $persona->id_persona,
            'user' => $cedula,
            'password' => $cedula, // El cast 'hashed' del modelo hashea automáticamente
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        Auth::logout();

        // Registrar log de logout
        try {
            if ($user) {
                SecurityAuditLog::create([
                    'user_id'       => $user->id_cedula,
                    'action'        => 'logout',
                    'resource_type' => 'user',
                    'resource_id'   => $user->id_cedula,
                    'description'   => 'Logout para usuario: ' . $user->user,
                    'ip_address'    => $request->ip(),
                    'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                    'status'        => 'success',
                    'metadata'      => null,
                    'created_at'    => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Evitar que un fallo en el log bloquee el cierre de sesión
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

