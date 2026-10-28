@extends('layouts.app')
@section('title', 'Cursos')
@section('content')
<div class="page-heading">
    <div><p class="eyebrow">Catálogo de aprendizaje</p><h1>Cursos</h1></div>
    @if(auth()->user()->isDocente())
        <a class="button" href="{{ route('cursos.create') }}">Nuevo curso</a>
    @endif
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
            @if(auth()->user()->isAdmin() || (auth()->user()->isDocente() && $course->user_id === auth()->id()))
                <a class="button secondary small" href="{{ route('cursos.edit', $course) }}">Editar</a>
                <form method="POST" action="{{ route('cursos.destroy', $course) }}" onsubmit="return confirm('¿Eliminar este curso?')">
                    @csrf @method('DELETE')
                    <button class="button danger small" type="submit">Eliminar</button>
                </form>
            @endif
        </div>
    </article>
@empty
    <div class="empty"><h2>Aún no hay cursos</h2><p>El catálogo se mostrará cuando un docente publique contenido.</p></div>
@endforelse
@endsection
