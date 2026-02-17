<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\AjustesController;
use App\Http\Controllers\FichasController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ReservasController;
use App\Http\Controllers\AmbientesController;

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
// Rutas de registro comentadas temporalmente (la vista se mantiene para uso futuro)
// Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
// Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rutas protegidas con middleware auth y HTTPS forzado en producción
Route::middleware(['auth', 'force.https'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Administrar Fichas (coordinador: solo index con búsqueda; exportación permitida)
    Route::get('/fichas', [FichasController::class, 'index'])->name('fichas.index');
    Route::get('/fichas/export', [FichasController::class, 'export'])->name('fichas.export');
    Route::get('/fichas/create', [FichasController::class, 'create'])->name('fichas.create')->middleware('coordinator.viewonly');
    Route::post('/fichas', [FichasController::class, 'store'])->name('fichas.store')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::get('/fichas/{id}/edit', [FichasController::class, 'edit'])->name('fichas.edit')->middleware('coordinator.viewonly');
    Route::put('/fichas/{id}', [FichasController::class, 'update'])->name('fichas.update')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::delete('/fichas/{id}', [FichasController::class, 'destroy'])->name('fichas.destroy')->middleware(['coordinator.viewonly', 'throttle:destroy']);
    
    // Ambientes (coordinador: solo index con búsqueda; exportación permitida)
    Route::get('/ambientes', [AmbientesController::class, 'index'])->name('ambientes.index');
    Route::get('/ambientes/disponibilidad', [AmbientesController::class, 'disponibilidad'])->name('ambientes.disponibilidad');
    Route::get('/ambientes/export', [AmbientesController::class, 'export'])->name('ambientes.export');
    
    // Reservas (Asignación de ambientes a fichas) — coordinador sin acceso a crear/editar/eliminar
    Route::get('/reservas/create', [ReservasController::class, 'create'])->name('reservas.create')->middleware('coordinator.viewonly');
    Route::post('/reservas', [ReservasController::class, 'store'])->name('reservas.store')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::get('/reservas/{id}/edit', [ReservasController::class, 'edit'])->name('reservas.edit')->middleware('coordinator.viewonly');
    Route::put('/reservas/{id}', [ReservasController::class, 'update'])->name('reservas.update')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::delete('/reservas/{id}', [ReservasController::class, 'destroy'])->name('reservas.destroy')->middleware(['coordinator.viewonly', 'throttle:destroy']);
    
    // Inventario (coordinador: solo index con búsqueda; exportación permitida)
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('/inventario/export', [InventarioController::class, 'export'])->name('inventario.export');
    Route::get('/inventario/create', [InventarioController::class, 'create'])->name('inventario.create')->middleware('coordinator.viewonly');
    Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::get('/inventario/{id}/edit', [InventarioController::class, 'edit'])->name('inventario.edit')->middleware('coordinator.viewonly');
    Route::put('/inventario/{id}', [InventarioController::class, 'update'])->name('inventario.update')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::delete('/inventario/{id}', [InventarioController::class, 'destroy'])->name('inventario.destroy')->middleware(['coordinator.viewonly', 'throttle:destroy']);
    
    // Ajustes
    Route::get('/ajustes', [AjustesController::class, 'index'])->name('ajustes.index');
    Route::put('/ajustes/nombre', [AjustesController::class, 'updateNombre'])->name('ajustes.update.nombre');
    Route::put('/ajustes/apellido', [AjustesController::class, 'updateApellido'])->name('ajustes.update.apellido');
    Route::put('/ajustes/correo', [AjustesController::class, 'updateCorreo'])->name('ajustes.update.correo');
    Route::put('/ajustes/telefono', [AjustesController::class, 'updateTelefono'])->name('ajustes.update.telefono');
    Route::put('/ajustes/usuario', [AjustesController::class, 'updateUsuario'])->name('ajustes.update.usuario');
    Route::put('/ajustes/contraseña', [AjustesController::class, 'updateContraseña'])->name('ajustes.update.contraseña');
    
    // Usuarios (solo administradores)
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
        Route::post('/users', [UsersController::class, 'store'])->name('users.store')->middleware('throttle:users');
        Route::get('/users/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UsersController::class, 'update'])->name('users.update')->middleware('throttle:users');
        Route::delete('/users/{id}', [UsersController::class, 'destroy'])->name('users.destroy')->middleware('throttle:users_destroy');
    });
});
