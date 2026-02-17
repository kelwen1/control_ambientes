<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SecurityAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

        return redirect()->intended('/dashboard');
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'id_cedula' => 'required|string|unique:users,id_cedula',
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'correo' => 'required|email|unique:users,correo',
            'telefono' => 'nullable|string|max:20',
            'user' => 'required|string|unique:users,user|max:255',
            'contraseña' => 'required|string|min:8|confirmed',
            'id_rol' => 'required|integer|in:1,2,3',
        ], [
            'id_cedula.required' => 'El número de cédula es obligatorio.',
            'id_cedula.unique' => 'Esta cédula ya está registrada en el sistema.',
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Debe ingresar un correo electrónico válido.',
            'correo.unique' => 'Este correo electrónico ya está registrado.',
            'user.required' => 'El nombre de usuario es obligatorio.',
            'user.unique' => 'Este nombre de usuario ya está en uso. Por favor elige otro.',
            'contraseña.required' => 'La contraseña es obligatoria.',
            'contraseña.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'contraseña.confirmed' => 'Las contraseñas no coinciden.',
            'id_rol.required' => 'Debe seleccionar un rol.',
            'id_rol.in' => 'El rol seleccionado no es válido.',
        ]);

        $user = User::create([
            'id_cedula' => $validated['id_cedula'],
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'correo' => $validated['correo'],
            'telefono' => $validated['telefono'] ?? null,
            'user' => $validated['user'],
            'contraseña' => Hash::make($validated['contraseña']),
            'id_rol' => $validated['id_rol'],
        ]);

        Auth::login($user);

        return redirect()->route('login');
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

