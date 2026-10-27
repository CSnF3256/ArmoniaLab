<?php

use App\Http\Controllers\AcordeController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEjercicioController;
use App\Http\Controllers\Admin\AdminTipoAcordeController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EjercicioController;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\ProgresoController;
use Illuminate\Support\Facades\Route;

// 1. Rutas Públicas
Route::get('/', function () {
    return view('home');
})->name('home');

// Rutas de Invitados (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->name('register.store');
});

// 2. Rutas Autenticadas (Estudiantes, Docentes y Administradores)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Core MVC Musical: Constructor y visualizador de acordes (requiere autenticación)
    Route::get('/acordes', [AcordeController::class, 'index'])->name('acordes.index');
    Route::match(['get', 'post'], '/acordes/calcular', [AcordeController::class, 'calcular'])->name('acordes.calcular');

    // Biblioteca personal de acordes (Core extendido)
    Route::post('/acordes/guardar', [AcordeController::class, 'guardar'])->name('acordes.guardar');
    Route::get('/acordes/biblioteca', [AcordeController::class, 'biblioteca'])->name('acordes.biblioteca');
    Route::delete('/acordes/biblioteca/{acorde}', [AcordeController::class, 'destroy'])->name('acordes.destroy');

    // Módulo de Cursos (existente)
    Route::resource('cursos', CourseController::class)->parameters(['cursos' => 'course']);

    // Módulo de Ejercicios con Corrección Automática
    Route::get('/ejercicios', [EjercicioController::class, 'index'])->name('ejercicios.index');
    Route::get('/ejercicios/{ejercicio}', [EjercicioController::class, 'show'])->name('ejercicios.show');
    Route::post('/ejercicios/{ejercicio}/responder', [EjercicioController::class, 'responder'])->name('ejercicios.responder');

    // Módulo de Progreso por Habilidad y Recomendaciones Personalizadas
    Route::get('/progreso', [ProgresoController::class, 'index'])->name('progreso.index');

    // API para Dropdowns Dependientes (en cascada)
    Route::get('/api/paises/{pais}/provincias', [GeoController::class, 'provincias'])->name('api.geo.provincias');
    Route::get('/api/provincias/{provincia}/ciudades', [GeoController::class, 'ciudades'])->name('api.geo.ciudades');
    Route::get('/api/cursos/{course}/lecciones', [GeoController::class, 'leccionesDeCurso'])->name('api.geo.lecciones');
});

// 3. Panel de Administración (Protegido por auth y rol admin/docente)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Gestión de Usuarios con Validación de Cédula (Back-End) y Dropdowns Dependientes
    Route::resource('usuarios', AdminUserController::class)->except(['show']);

    // Creación y Gestión de Ejercicios con Dropdowns Dependientes Curso -> Lección
    Route::resource('ejercicios', AdminEjercicioController::class)->only(['index', 'create', 'store', 'destroy']);

    // Gestión del Core Musical con Validación Back-End de Intervalos
    Route::resource('tipos-acordes', AdminTipoAcordeController::class)->only(['index', 'create', 'store', 'destroy']);
});
