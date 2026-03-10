<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AjustesController;
use App\Http\Controllers\FichasController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ReservasController;
use App\Http\Controllers\AmbientesController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\TabSessionController;

Route::get('/', function () {
    return view('welcome');
});

// Manual de usuario (público, accesible desde el login)
Route::get('/manual-usuario', function () {
    return view('manual-usuario');
})->name('manual.usuario');

// Rutas de autenticación con rate limiting (límites en config/throttle.php y .env THROTTLE_*)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('throttle:login_get');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login_post');
// Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
// Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
| Rutas protegidas: prefijo "s" (sistema) y paths neutros para no exponer
| estructura interna en la barra de direcciones (seguridad / ofuscación).
| Los nombres de rutas (route('inventario.create'), etc.) no cambian.
*/
Route::prefix('s')->middleware(['auth', 'force.https'])->group(function () {
    Route::post('/tab/register', [TabSessionController::class, 'register'])->name('tab.register');
    Route::post('/tab/unregister', [TabSessionController::class, 'unregister'])->name('tab.unregister');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Instructor: mi jornada (tablero L-V y detalle por día)
    Route::get('/mi-jornada', [InstructorController::class, 'tablero'])->name('instructor.tablero');
    Route::get('/mi-jornada/{dia}', [InstructorController::class, 'detalleDia'])->name('instructor.detalle-dia')->where('dia', 'lunes|martes|miercoles|jueves|viernes');
    
    // Fichas → formacion
    Route::get('/formacion', [FichasController::class, 'index'])->name('fichas.index');
    Route::get('/formacion/export', [FichasController::class, 'export'])->name('fichas.export');
    Route::get('/formacion/nuevo', [FichasController::class, 'create'])->name('fichas.create')->middleware('coordinator.viewonly');
    Route::post('/formacion', [FichasController::class, 'store'])->name('fichas.store')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::get('/formacion/{id}/editar', [FichasController::class, 'edit'])->name('fichas.edit')->middleware('coordinator.viewonly');
    Route::put('/formacion/{id}', [FichasController::class, 'update'])->name('fichas.update')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::delete('/formacion/{id}', [FichasController::class, 'destroy'])->name('fichas.destroy')->middleware(['coordinator.viewonly', 'throttle:destroy']);
    
    // Ambientes → espacios
    Route::get('/espacios', [AmbientesController::class, 'index'])->name('ambientes.index');
    Route::get('/espacios/disponibilidad', [AmbientesController::class, 'disponibilidad'])->name('ambientes.disponibilidad');
    Route::get('/espacios/export', [AmbientesController::class, 'export'])->name('ambientes.export');
    
    // Reservas → asignacion
    Route::get('/asignacion/nuevo', [ReservasController::class, 'create'])->name('reservas.create')->middleware('coordinator.viewonly');
    Route::post('/asignacion', [ReservasController::class, 'store'])->name('reservas.store')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::get('/asignacion/{id}/editar', [ReservasController::class, 'edit'])->name('reservas.edit')->middleware('coordinator.viewonly');
    Route::put('/asignacion/{id}', [ReservasController::class, 'update'])->name('reservas.update')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::delete('/asignacion/{id}', [ReservasController::class, 'destroy'])->name('reservas.destroy')->middleware(['coordinator.viewonly', 'throttle:destroy']);
    
    // Ajustes → cuenta
    Route::get('/cuenta', [AjustesController::class, 'index'])->name('ajustes.index');
    Route::put('/cuenta/nombre', [AjustesController::class, 'updateNombre'])->name('ajustes.update.nombre');
    Route::put('/cuenta/apellido', [AjustesController::class, 'updateApellido'])->name('ajustes.update.apellido');
    Route::put('/cuenta/correo', [AjustesController::class, 'updateCorreo'])->name('ajustes.update.correo');
    Route::put('/cuenta/telefono', [AjustesController::class, 'updateTelefono'])->name('ajustes.update.telefono');
    Route::put('/cuenta/usuario', [AjustesController::class, 'updateUsuario'])->name('ajustes.update.usuario');
    Route::put('/cuenta/contraseña', [AjustesController::class, 'updateContraseña'])->name('ajustes.update.contraseña');
    
    // Usuarios (solo admin) → administracion
    Route::middleware('admin')->group(function () {
        Route::get('/administracion', [UsersController::class, 'index'])->name('users.index');
        Route::get('/administracion/nuevo', [UsersController::class, 'create'])->name('users.create');
        Route::post('/administracion', [UsersController::class, 'store'])->name('users.store')->middleware('throttle:users');
        Route::get('/administracion/{id}/editar', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/administracion/{id}', [UsersController::class, 'update'])->name('users.update')->middleware('throttle:users');
        Route::delete('/administracion/{id}', [UsersController::class, 'destroy'])->name('users.destroy')->middleware('throttle:users_destroy');
    });
});
