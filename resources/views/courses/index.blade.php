@extends('layouts.app')
@section('title', 'Mis cursos')
@section('content')
<div class="page-heading">
    <div><p class="eyebrow">Sección protegida</p><h1>Mis cursos</h1></div>
    <a class="button" href="{{ route('cursos.create') }}">Nuevo curso</a>
</div>

@forelse ($courses as $course)
    <article class="course-card">
        <div>
            <div class="badges"><span>{{ $course->level }}</span><span>{{ $course->status }}</span></div>
            <h2>{{ $course->title }}</h2>
            <p>{{ Str::limit($course->description, 150) }}</p>
        </div>
        <div class="actions">
            <a class="button secondary small" href="{{ route('cursos.show', $course) }}">Ver</a>
            <a class="button secondary small" href="{{ route('cursos.edit', $course) }}">Editar</a>
            <form method="POST" action="{{ route('cursos.destroy', $course) }}" onsubmit="return confirm('¿Eliminar este curso?')">
                @csrf @method('DELETE')
                <button class="button danger small" type="submit">Eliminar</button>
            </form>
        </div>
    </article>
@empty
    <div class="empty"><h2>Aún no hay cursos</h2><p>Crea el primero para demostrar el CRUD.</p></div>
@endforelse
@endsection
