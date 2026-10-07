@extends('layouts.app')

@section('title', 'ArmoníaLab')

@section('content')
<section class="hero">
    <div class="hero-copy">
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
    </div>
    <div class="hero-image">
        <img src="{{ asset('images/music-hero.png') }}" alt="Ilustración de un teclado, audífonos y notas musicales">
    </div>
</section>

@endsection
