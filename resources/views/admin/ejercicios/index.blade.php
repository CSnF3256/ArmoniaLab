@extends('layouts.app')

@section('title', 'Gestión de Ejercicios | Admin ArmoníaLab')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">Panel de Administración</span>
        <h1>Gestión de Ejercicios y Autocorrección</h1>
        <p class="subtitle">Banco de preguntas interactivo con asignación jerárquica Curso → Lección y corrección en el servidor.</p>
    </div>
    <div class="actions">
        <a href="{{ route('admin.ejercicios.create') }}" class="button">➕ Crear Ejercicio con Cascada</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Pregunta</th>
                    <th>Curso / Lección</th>
                    <th>Habilidad</th>
                    <th>Dificultad</th>
                    <th>Respuesta Correcta</th>
                    <th>Puntos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ejercicios as $ej)
                    <tr>
                        <td><strong>{{ Str::limit($ej->pregunta, 50) }}</strong></td>
                        <td>
                            <div><strong>{{ $ej->leccion?->course?->title ?? 'Sin curso' }}</strong></div>
                            <span class="text-sm text-muted">↳ {{ $ej->leccion?->titulo ?? 'Sin lección' }}</span>
                        </td>
                        <td><span class="badge-skill small">{{ $ej->habilidad }}</span></td>
                        <td><span class="badge-difficulty {{ strtolower($ej->dificultad) }}">{{ $ej->dificultad }}</span></td>
                        <td><code class="correct-text">{{ $ej->respuesta_correcta }}</code></td>
                        <td>{{ $ej->puntos }} pts</td>
                        <td>
                            <a href="{{ route('admin.ejercicios.edit', $ej) }}" class="button small secondary">Editar</a>
                            <form method="POST" action="{{ route('admin.ejercicios.destroy', $ej) }}" class="inline-form" onsubmit="return confirm('¿Eliminar este ejercicio?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button small danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper mt-4">
        {{ $ejercicios->links() }}
    </div>
</div>
@endsection
