@extends('layouts.app')
@section('title', $course->title)
@section('content')
<article class="detail-card">
    <div class="badges"><span>{{ $course->level }}</span><span>{{ $course->status }}</span></div>
    <h1>{{ $course->title }}</h1><p>{{ $course->description }}</p>
    <div class="actions">
        @if(auth()->user()->isAdmin() || (auth()->user()->isDocente() && $course->user_id === auth()->id()))
            <a class="button" href="{{ route('cursos.edit', $course) }}">Editar</a>
        @endif
        <a class="button secondary" href="{{ route('cursos.index') }}">Volver</a>
    </div>
</article>
@endsection
