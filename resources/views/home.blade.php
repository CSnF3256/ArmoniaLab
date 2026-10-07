@extends('layouts.app')

@section('title', 'ArmoníaLab')

@section('content')
<section class="hero">
    <p class="eyebrow">Proyecto de Ingeniería Web</p>
    <h1>Aprende teoría musical paso a paso</h1>
    <p>ArmoníaLab organiza cursos de música y sirve como base para incorporar lecciones, ejercicios y análisis armónico.</p>
    <div class="actions">
        @auth
            <a class="button" href="{{ route('cursos.index') }}">Entrar a mis cursos</a>
        @else
            <a class="button" href="{{ route('login') }}">Iniciar sesión</a>
            <a class="button secondary" href="{{ route('register') }}">Registrarme</a>
        @endauth
    </div>
</section>

@endsection
