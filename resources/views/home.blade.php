@extends('layouts.app')

@section('title', 'ArmoníaLab - Plataforma de Teoría Musical y Core Armónico')

@section('content')
<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">Universidad de Las Américas | Ingeniería Web</p>
        <h1>Aprende teoría musical y visualiza acordes en tiempo real</h1>
        <p>ArmoníaLab conecta la teoría armónica con la práctica interactiva: calcula notas mediante aritmética modular sobre las 12 clases cromáticas, resuelve ejercicios con corrección automática en servidor y recibe rutas de práctica adaptadas a tu progreso.</p>
        <div class="actions">
            <a class="button" href="{{ route('acordes.index') }}">🎹 Explorar Constructor de Acordes</a>
            @auth
                <a class="button secondary" href="{{ route('ejercicios.index') }}">✍️ Resolver Ejercicios</a>
                <a class="button secondary" href="{{ route('progreso.index') }}">📊 Mi Progreso</a>
            @else
                <a class="button secondary" href="{{ route('login') }}">Iniciar Sesión</a>
            @endauth
        </div>
    </div>
    <div class="hero-image">
        <img src="{{ asset('images/music-hero.png') }}" alt="Ilustración de un teclado, audífonos y notas musicales">
    </div>
</section>

<section class="features mt-5">
    <article class="card">
        <div class="module-icon">🎹</div>
        <h2>Core MVC: Generador de Acordes</h2>
        <p>Recibe una fundamental y un tipo de acorde, consulta sus intervalos en semitonos, calcula las notas resultantes y las presenta en un teclado virtual con síntesis polifónica en Web Audio API.</p>
        <a href="{{ route('acordes.index') }}" class="button small secondary mt-3">Ver Constructor →</a>
    </article>

    <article class="card">
        <div class="module-icon">🧠</div>
        <h2>Autocorrección y Recomendaciones</h2>
        <p>Ejercicios evaluados en el Back-End con registro de intentos. El sistema detecta tus habilidades con menor precisión y propone sesiones de práctica personalizadas para superar debilidades.</p>
        @auth
            <a href="{{ route('ejercicios.index') }}" class="button small secondary mt-3">Ir a Ejercicios →</a>
        @else
            <a href="{{ route('login') }}" class="button small secondary mt-3">Ingresar para Practicar →</a>
        @endauth
    </article>

    <article class="card">
        <div class="module-icon">🛡️</div>
        <h2>Arquitectura y Reglas Back-End</h2>
        <p>Validación obligatoria en servidor de datos sensibles (Cédula ecuatoriana con Módulo 10 y fórmulas armónicas) y referencias mediante <strong>Dropdowns Dependientes en Cascada</strong>.</p>
        @auth
            @if(auth()->user()->isAdmin() || auth()->user()->isDocente())
                <a href="{{ route('admin.dashboard') }}" class="button small secondary mt-3">Panel de Administración →</a>
            @else
                <a href="{{ route('cursos.index') }}" class="button small secondary mt-3">Ver Mis Cursos →</a>
            @endif
        @else
            <a href="{{ route('login') }}" class="button small secondary mt-3">Acceso Administrativo →</a>
        @endauth
    </article>
</section>
@endsection
