@extends('layouts.app')

@section('title', 'Ejercicios de Teoría Musical | ArmoníaLab')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">Evaluación y Aprendizaje</span>
        <h1>Ejercicios de Teoría Musical</h1>
        <p class="subtitle">Práctica con corrección automática inmediata en el servidor, registro de intentos y progreso por habilidad.</p>
    </div>
    <div class="actions">
        <a href="{{ route('progreso.index') }}" class="button secondary">📊 Ver Mi Progreso por Habilidad</a>
    </div>
</div>

<!-- Sección de Práctica Inteligente Sugerida -->
<div class="card smart-practice-card">
    <div class="smart-practice-header">
        <div>
            <span class="smart-badge-top">🚀 Práctica Inteligente Sugerida</span>
            <p class="smart-desc">Aún no has resuelto ejercicios. Comienza con una práctica guiada para evaluar tu nivel armónico.</p>
        </div>
        <div class="smart-precision-box">
            <span class="precision-title">Precisión Global</span>
            <span class="precision-value">0%</span>
        </div>
    </div>

    <!-- Rejilla interna -->
    <div class="smart-grid">
        <!-- Tarjeta Sugerida 1 -->
        <div class="smart-item-card">
            <div>
                <span class="smart-category">Tríadas Mayores</span>
                <h4 class="smart-title">¿Cuáles son las notas integrantes de la tríada de Do Mayor (C)?</h4>
                <p class="smart-sub">Evaluación diagnóstica inicial en Tríadas Mayores</p>
            </div>
            <a href="#" class="button small smart-btn">Resolver Ahora →</a>
        </div>

        <!-- Tarjeta Sugerida 2 -->
        <div class="smart-item-card">
            <div>
                <span class="smart-category">Tríadas Mayores</span>
                <h4 class="smart-title">¿Cuáles son las notas de la tríada de Fa Mayor (F)?</h4>
                <p class="smart-sub">Evaluación diagnóstica inicial en Tríadas Mayores</p>
            </div>
            <a href="#" class="button small smart-btn">Resolver Ahora →</a>
        </div>

        <!-- Tarjeta Sugerida 3 -->
        <div class="smart-item-card">
            <div>
                <span class="smart-category">Tríadas Menores</span>
                <h4 class="smart-title">¿Cuáles son las notas integrantes del acorde de La menor (Am)?</h4>
                <p class="smart-sub">Evaluación diagnóstica inicial en Tríadas Menores</p>
            </div>
            <a href="#" class="button small smart-btn">Resolver Ahora →</a>
        </div>
    </div>
</div>

<!-- Listado general de ejercicios -->
<div class="exercises-grid">
    @foreach($ejercicios ?? [] as $ej)
        <div class="card exercise-card">
            <div>
                <div class="exercise-card-header">
                    <span class="badge-skill">{{ $ej->habilidad }}</span>
                    <span class="badge-difficulty {{ strtolower($ej->dificultad) }}">{{ $ej->dificultad }}</span>
                </div>
                <h3 class="exercise-question">{{ $ej->pregunta }}</h3>
            </div>
            <div>
                <div class="exercise-meta">
                    <span>📚 {{ $ej->leccion?->titulo ?? 'Práctica general' }}</span>
                    <span>🏆 {{ $ej->puntos }} pts</span>
                </div>
                <a href="{{ route('ejercicios.show', $ej) }}" class="button small full-width">Comenzar Ejercicio →</a>
            </div>
        </div>
    @endforeach
</div>
@endsection