<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AjustesController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $user = Auth::user();
        
        return view('ajustes.index', [
            'user' => $user
        ]);
    }

    /**
     * Update user's name.
     */
    public function updateNombre(Request $request)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:25',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/',
            ],
            'contraseña_actual' => 'required|string',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 25 caracteres.',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios.',
        ]);

        $user = Auth::user();

        // Verificar contraseña
        if (!Hash::check($request->contraseña_actual, $user->contraseña)) {
            return back()->withErrors(['contraseña_actual' => 'La contraseña actual es incorrecta.'])->withInput();
        }

        $user->nombre = $request->nombre;
        $user->save();

        return redirect()->route('ajustes.index')->with('success', 'Nombre actualizado correctamente.');
    }

    /**
     * Update user's last name.
     */
    public function updateApellido(Request $request)
    {
        $request->validate([
            'apellido' => [
                'required',
                'string',
                'max:25',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/',
            ],
            'contraseña_actual' => 'required|string',
        ], [
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.max' => 'El apellido no puede tener más de 25 caracteres.',
            'apellido.regex' => 'El apellido solo puede contener letras y espacios.',
        ]);

        $user = Auth::user();

        // Verificar contraseña
        if (!Hash::check($request->contraseña_actual, $user->contraseña)) {
            return back()->withErrors(['contraseña_actual' => 'La contraseña actual es incorrecta.'])->withInput();
        }

        $user->apellido = $request->apellido;
        $user->save();

        return redirect()->route('ajustes.index')->with('success', 'Apellido actualizado correctamente.');
    }

    /**
     * Update user's email.
     */
    public function updateCorreo(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'correo' => [
                'required',
                'email',
                'max:100',
                'unique:users,correo,' . $user->id_cedula . ',id_cedula',
                'regex:/^[^\s]+@(gmail|hotmail)\.com$/i',
            ],
            'contraseña_actual' => 'required|string',
        ], [
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El correo electrónico debe tener un formato válido.',
            'correo.max' => 'El correo electrónico no puede tener más de 100 caracteres.',
            'correo.unique' => 'Este correo electrónico ya está en uso.',
            'correo.regex' => 'El correo electrónico debe ser de gmail.com o hotmail.com y no puede contener espacios.',
        ]);

        // Verificar contraseña
        if (!Hash::check($request->contraseña_actual, $user->contraseña)) {
            return back()->withErrors(['contraseña_actual' => 'La contraseña actual es incorrecta.'])->withInput();
        }

        $user->correo = $request->correo;
        $user->save();

        return redirect()->route('ajustes.index')->with('success', 'Correo electrónico actualizado correctamente.');
    }

    /**
     * Update user's phone.
     */
    public function updateTelefono(Request $request)
    {
        $request->validate([
            'telefono' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^[0-9]+$/',
            ],
            'contraseña_actual' => 'required|string',
        ], [
            'telefono.max' => 'El teléfono no puede tener más de 10 caracteres.',
            'telefono.regex' => 'El teléfono solo puede contener números, sin espacios ni caracteres especiales.',
        ]);

        $user = Auth::user();

        // Verificar contraseña
        if (!Hash::check($request->contraseña_actual, $user->contraseña)) {
            return back()->withErrors(['contraseña_actual' => 'La contraseña actual es incorrecta.'])->withInput();
        }

        $user->telefono = $request->telefono ?? null;
        $user->save();

        return redirect()->route('ajustes.index')->with('success', 'Teléfono actualizado correctamente.');
    }

    /**
     * Update user's username.
     */
    public function updateUsuario(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'user' => [
                'required',
                'string',
                'max:15',
                'unique:users,user,' . $user->id_cedula . ',id_cedula',
                'regex:/^[a-zA-Z0-9]+$/',
            ],
            'contraseña_actual' => 'required|string',
        ], [
            'user.required' => 'El usuario es obligatorio.',
            'user.max' => 'El usuario no puede tener más de 15 caracteres.',
            'user.unique' => 'Este usuario ya está en uso.',
            'user.regex' => 'El usuario solo puede contener letras y números, sin espacios ni caracteres especiales.',
        ]);

        // Verificar contraseña
        if (!Hash::check($request->contraseña_actual, $user->contraseña)) {
            return back()->withErrors(['contraseña_actual' => 'La contraseña actual es incorrecta.'])->withInput();
        }

        $user->user = $request->user;
        $user->save();

        return redirect()->route('ajustes.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Update user's password.
     */
    public function updateContraseña(Request $request)
    {
        $request->validate([
            'contraseña_actual' => 'required|string',
            'contraseña_nueva' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
            'contraseña_nueva_confirmacion' => 'required|string|same:contraseña_nueva',
        ], [
            'contraseña_nueva.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'contraseña_nueva.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un símbolo especial (@$!%*?&).',
            'contraseña_nueva_confirmacion.same' => 'Las contraseñas nuevas no coinciden.',
        ]);

        $user = Auth::user();

        // Verificar contraseña actual
        if (!Hash::check($request->contraseña_actual, $user->contraseña)) {
            return back()->withErrors(['contraseña_actual' => 'La contraseña actual es incorrecta.'])->withInput();
        }

        // Actualizar contraseña
        $user->contraseña = Hash::make($request->contraseña_nueva);
        $user->save();

        return redirect()->route('ajustes.index')->with('success', 'Contraseña actualizada correctamente.');
    }
}

