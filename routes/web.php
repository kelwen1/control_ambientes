<?php

use App\Http\Controllers\AjustesController;
use App\Http\Controllers\AmbientesController;
use App\Http\Controllers\AmbientesCrudController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompetenciasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FichasController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\NivelProgramaController;
use App\Http\Controllers\ProgramasController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ReservaLiberacionController;
use App\Http\Controllers\ReservasController;
use App\Http\Controllers\ResultadosController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

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
| Los nombres de rutas públicas del sistema no cambian.
*/
Route::prefix('s')->middleware(['auth', 'force.https'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');

    // Instructor: mi jornada, detalle por día y reporte PDF propio (por año/mes/semana)
    Route::middleware('instructor')->group(function () {
        Route::get('/mi-jornada', [InstructorController::class, 'tablero'])->name('instructor.tablero');
        Route::get('/mi-jornada/{dia}', [InstructorController::class, 'detalleDia'])->name('instructor.detalle-dia')->where('dia', 'lunes|martes|miercoles|jueves|viernes|sabado|domingo');

        Route::get('/reportes/mis-reservas', [InstructorController::class, 'reporteReservas'])->name('instructor.reporte-reservas');
        Route::get('/reportes/mis-reservas/export', [InstructorController::class, 'exportReservas'])->name('instructor.export-reservas');
        Route::get('/reportes/mis-reservas/filtro', [InstructorController::class, 'reporteReservasFiltro'])->name('instructor.reporte-reservas-filtro');
        Route::get('/reportes/mis-reservas/descargar', [InstructorController::class, 'exportReservasFiltrado'])->name('instructor.export-reservas-filtro');
    });

    Route::middleware(['admin.or.coordinatorL', 'throttle:write'])->group(function () {
        Route::post('/reserva/liberar-dia', [ReservaLiberacionController::class, 'liberarDia'])->name('reservas.liberar-dia');
        Route::post('/reserva/liberar-festivos', [ReservaLiberacionController::class, 'liberarFestivos'])->name('reservas.liberar-festivos');
        Route::post('/reserva/revertir-dia', [ReservaLiberacionController::class, 'revertirDiaLiberado'])->name('reservas.revertir-dia');
    });

    // Fichas → formacion
    Route::get('/formacion', [FichasController::class, 'index'])->name('fichas.index');
    Route::get('/formacion/export', [FichasController::class, 'export'])->name('fichas.export');
    Route::get('/formacion/export-excel', [FichasController::class, 'exportExcel'])->name('fichas.export-excel');
    Route::get('/formacion/export-programas', [ProgramasController::class, 'export'])->name('programas.export');
    Route::get('/formacion/export-programas-excel', [ProgramasController::class, 'exportExcel'])->name('programas.export-excel');
    Route::get('/formacion/export-competencias', [CompetenciasController::class, 'export'])->name('competencias.export');
    Route::get('/formacion/export-competencias-excel', [CompetenciasController::class, 'exportExcel'])->name('competencias.export-excel');
    Route::get('/formacion/fechas-por-programa', [FichasController::class, 'fechasPorPrograma'])->name('fichas.fechas-por-programa')->middleware('coordinator.viewonly');
    Route::get('/formacion/nuevo', [FichasController::class, 'create'])->name('fichas.create')->middleware('coordinator.viewonly');
    Route::post('/formacion', [FichasController::class, 'store'])->name('fichas.store')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::get('/formacion/{id}/editar', [FichasController::class, 'edit'])->name('fichas.edit')->middleware('coordinator.viewonly');
    Route::put('/formacion/{id}', [FichasController::class, 'update'])->name('fichas.update')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::delete('/formacion/{id}', [FichasController::class, 'destroy'])->name('fichas.destroy')->middleware(['coordinator.viewonly', 'throttle:destroy']);

    // Ambientes → espacios
    Route::get('/espacios', [AmbientesController::class, 'index'])->name('ambientes.index');
    Route::get('/espacios/reserva-original/{id}', [AmbientesController::class, 'reservaOriginal'])->name('ambientes.reserva-original');
    Route::get('/espacios/disponibilidad', [AmbientesController::class, 'disponibilidad'])->name('ambientes.disponibilidad');
    Route::get('/espacios/disponibilidad-ambiente', [AmbientesController::class, 'disponibilidadAmbiente'])->name('ambientes.disponibilidad-ambiente');
    Route::get('/espacios/export', [AmbientesController::class, 'export'])->name('ambientes.export');
    Route::get('/espacios/export-excel', [AmbientesController::class, 'exportExcel'])->name('ambientes.export-excel');
    Route::get('/espacios/gestion-export', [AmbientesCrudController::class, 'export'])->name('ambientes.gestion.export');
    Route::get('/espacios/gestion-export-excel', [AmbientesCrudController::class, 'exportExcel'])->name('ambientes.gestion.export-excel');

    // Reservas → asignacion
    Route::get('/asignacion/nuevo', [ReservasController::class, 'create'])->name('reservas.create')->middleware('coordinator.viewonly');
    Route::post('/asignacion', [ReservasController::class, 'store'])->name('reservas.store')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::get('/asignacion/{id}/editar', [ReservasController::class, 'edit'])->name('reservas.edit')->middleware('coordinator.viewonly');
    Route::put('/asignacion/{id}', [ReservasController::class, 'update'])->name('reservas.update')->middleware(['coordinator.viewonly', 'throttle:write']);
    Route::delete('/asignacion/{id}', [ReservasController::class, 'destroy'])->name('reservas.destroy')->middleware(['coordinator.viewonly', 'throttle:destroy']);
    Route::post('/asignacion/eliminar-lote', [ReservasController::class, 'destroyLote'])->name('reservas.destroy-lote')->middleware(['coordinator.viewonly', 'throttle:destroy']);

    // Ajustes → cuenta
    Route::get('/cuenta', [AjustesController::class, 'index'])->name('ajustes.index');
    Route::put('/cuenta/nombre', [AjustesController::class, 'updateNombre'])->name('ajustes.update.nombre');
    Route::put('/cuenta/apellido', [AjustesController::class, 'updateApellido'])->name('ajustes.update.apellido');
    Route::put('/cuenta/correo', [AjustesController::class, 'updateCorreo'])->name('ajustes.update.correo');
    Route::put('/cuenta/telefono', [AjustesController::class, 'updateTelefono'])->name('ajustes.update.telefono');
    Route::put('/cuenta/usuario', [AjustesController::class, 'updateUsuario'])->name('ajustes.update.usuario');
    Route::put('/cuenta/contraseña', [AjustesController::class, 'updateContraseña'])->name('ajustes.update.contraseña');

    // Usuarios (solo administrador)
    Route::middleware('admin')->group(function () {
        Route::get('/administracion/actualizacion-roles', [UsersController::class, 'showRoleUpdate'])->name('users.role-update');
        Route::get('/administracion/buscar-rol', [UsersController::class, 'lookupCedulaForRoleUpdate'])->name('users.role-lookup');
        Route::put('/administracion/rol', [UsersController::class, 'updateRole'])->name('users.role-apply')->middleware('throttle:users');
        Route::get('/administracion', [UsersController::class, 'index'])->name('users.index');
        Route::get('/administracion/nuevo', [UsersController::class, 'create'])->name('users.create');
        Route::post('/administracion', [UsersController::class, 'store'])->name('users.store')->middleware('throttle:users');
        Route::get('/administracion/{id}/editar', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/administracion/{id}', [UsersController::class, 'update'])->name('users.update')->middleware('throttle:users');
        Route::delete('/administracion/{id}', [UsersController::class, 'destroy'])->name('users.destroy')->middleware('throttle:users_destroy');
    });

    // Gestión de ambientes, programas, competencias, resultados (admin, coordinador L; coordinador normal: solo index GET)
    Route::middleware('catalog.access')->group(function () {
        Route::get('/espacios/gestion', [AmbientesCrudController::class, 'index'])->name('ambientes.gestion.index');
        Route::get('/espacios/gestion/nuevo', [AmbientesCrudController::class, 'create'])->name('ambientes.gestion.create');
        Route::get('/espacios/gestion/verificar-numero-ambiente', [AmbientesCrudController::class, 'verificarNumeroAmbiente'])->name('ambientes.gestion.verificar-numero');
        Route::post('/espacios/gestion', [AmbientesCrudController::class, 'store'])->name('ambientes.gestion.store');
        Route::get('/espacios/gestion/{id}/editar', [AmbientesCrudController::class, 'edit'])->name('ambientes.gestion.edit');
        Route::put('/espacios/gestion/{id}', [AmbientesCrudController::class, 'update'])->name('ambientes.gestion.update');
        Route::delete('/espacios/gestion/{id}', [AmbientesCrudController::class, 'destroy'])->name('ambientes.gestion.destroy');

        Route::get('/formacion/niveles-programa', [NivelProgramaController::class, 'index'])->name('niveles-programa.index');
        Route::get('/formacion/niveles-programa/nuevo', [NivelProgramaController::class, 'create'])->name('niveles-programa.create');
        Route::post('/formacion/niveles-programa', [NivelProgramaController::class, 'store'])->name('niveles-programa.store');
        Route::get('/formacion/niveles-programa/{id}/editar', [NivelProgramaController::class, 'edit'])->name('niveles-programa.edit');
        Route::put('/formacion/niveles-programa/{id}', [NivelProgramaController::class, 'update'])->name('niveles-programa.update');
        Route::delete('/formacion/niveles-programa/{id}', [NivelProgramaController::class, 'destroy'])->name('niveles-programa.destroy');

        Route::get('/formacion/programas', [ProgramasController::class, 'index'])->name('programas.index');
        Route::get('/formacion/programas/nuevo', [ProgramasController::class, 'create'])->name('programas.create');
        Route::post('/formacion/programas', [ProgramasController::class, 'store'])->name('programas.store');
        Route::get('/formacion/programas/{id}/editar', [ProgramasController::class, 'edit'])->name('programas.edit');
        Route::put('/formacion/programas/{id}', [ProgramasController::class, 'update'])->name('programas.update');
        Route::delete('/formacion/programas/{id}', [ProgramasController::class, 'destroy'])->name('programas.destroy');

        Route::get('/formacion/competencias', [CompetenciasController::class, 'index'])->name('competencias.index');
        Route::get('/formacion/competencias/nuevo', [CompetenciasController::class, 'create'])->name('competencias.create');
        Route::post('/formacion/competencias', [CompetenciasController::class, 'store'])->name('competencias.store');
        Route::get('/formacion/competencias/{id}/editar', [CompetenciasController::class, 'edit'])->name('competencias.edit');
        Route::put('/formacion/competencias/{id}', [CompetenciasController::class, 'update'])->name('competencias.update');
        Route::delete('/formacion/competencias/{id}', [CompetenciasController::class, 'destroy'])->name('competencias.destroy');

        Route::get('/formacion/resultados', [ResultadosController::class, 'index'])->name('resultados.index');
        // {competencia?} permite /nuevo/12 además de ?competencia=12 (más visible y estable en enlaces)
        Route::get('/formacion/resultados/nuevo/{competencia?}', [ResultadosController::class, 'create'])
            ->where(['competencia' => '[0-9]+'])
            ->name('resultados.create');
        Route::post('/formacion/resultados', [ResultadosController::class, 'store'])->name('resultados.store');
        Route::get('/formacion/resultados/{id}/editar', [ResultadosController::class, 'edit'])->name('resultados.edit');
        Route::put('/formacion/resultados/{id}', [ResultadosController::class, 'update'])->name('resultados.update');
        Route::delete('/formacion/resultados/{id}', [ResultadosController::class, 'destroy'])->name('resultados.destroy');
    });
});
