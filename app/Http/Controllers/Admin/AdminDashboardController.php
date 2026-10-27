<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Ejercicio;
use App\Models\Intento;
use App\Models\TipoAcorde;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_usuarios' => User::count(),
            'total_estudiantes' => User::where('role', 'estudiante')->count(),
            'total_docentes' => User::whereIn('role', ['docente', 'admin'])->count(),
            'total_cursos' => Course::count(),
            'total_ejercicios' => Ejercicio::count(),
            'total_tipos_acordes' => TipoAcorde::count(),
            'total_intentos' => Intento::count(),
            'tasa_acierto_global' => Intento::count() > 0
                ? round((Intento::where('es_correcta', true)->count() / Intento::count()) * 100, 1)
                : 0,
        ];

        $ultimosUsuarios = User::with(['pais', 'provincia', 'ciudad'])->latest()->take(5)->get();
        $ultimosIntentos = Intento::with(['user', 'ejercicio'])->latest()->take(8)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'ultimosUsuarios' => $ultimosUsuarios,
            'ultimosIntentos' => $ultimosIntentos,
        ]);
    }
}
