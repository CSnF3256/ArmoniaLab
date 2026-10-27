@extends('layouts.app')

@section('title', 'Ejercicios Interactivos con Autocorrección | ArmoníaLab')

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

<!-- Banner de Práctica Personalizada Inteligente (Recomendación del Docente) -->
@if(!empty($diagnostico['recomendaciones']))
    <div class="card recommendation-hero-card mb-4">
        <div class="rec-header">
            <span class="rec-badge">🎯 Práctica Inteligente Sugerida</span>
            <span class="rec-score">Precisión Global: <strong>{{ $diagnostico['porcentaje_global'] }}%</strong></span>
        </div>
        <h3>{{ $diagnostico['mensaje_diagnostico'] }}</h3>

        <div class="rec-items-grid mt-3">
            @foreach($diagnostico['recomendaciones'] as $rec)
                <div class="rec-chip-card">
                    <div class="rec-chip-content">
                        <span class="skill-tag">{{ $rec['ejercicio']->habilidad }}</span>
                        <h4>{{ Str::limit($rec['ejercicio']->pregunta, 65) }}</h4>
                        <p class="rec-reason">{{ $rec['motivo'] }}</p>
                    </div>
                    <a href="{{ route('ejercicios.show', $rec['ejercicio']) }}" class="button small">Resolver Ahora →</a>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Filtro de Habilidades -->
<div class="skills-filter-bar mb-4">
    <a href="{{ route('ejercicios.index') }}" class="filter-pill {{ empty($habilidadSeleccionada) ? 'active' : '' }}">
        Todas las Habilidades
    </a>
    @foreach($habilidades as $habilidad)
        <a href="{{ route('ejercicios.index', ['habilidad' => $habilidad]) }}" class="filter-pill {{ $habilidadSeleccionada === $habilidad ? 'active' : '' }}">
            {{ $habilidad }}
        </a>
    @endforeach
</div>

<!-- Catálogo de Ejercicios -->
<div class="exercises-grid">
    @forelse($ejercicios as $ejercicio)
        @php
            $intentos = $intentosUsuario->get($ejercicio->id, collect());
            $ultimoIntento = $intentos->first();
            $haAcertado = $intentos->contains('es_correcta', true);
        @endphp

        <div class="card exercise-card {{ $haAcertado ? 'passed' : ($ultimoIntento ? 'failed' : '') }}">
            <div class="exercise-card-header">
                <span class="badge-skill">{{ $ejercicio->habilidad }}</span>
                <span class="badge-difficulty {{ strtolower($ejercicio->dificultad) }}">{{ $ejercicio->dificultad }}</span>
            </div>

            <h3 class="exercise-question">{{ $ejercicio->pregunta }}</h3>

            <div class="exercise-meta">
                <span class="meta-item">📚 {{ $ejercicio->leccion?->titulo ?? 'Lección General' }}</span>
                <span class="meta-item">🏆 {{ $ejercicio->puntos }} pts</span>
            </div>

            <div class="exercise-status-bar mt-3">
                @if($haAcertado)
                    <span class="status-chip success">✓ Superado</span>
                @elseif($ultimoIntento)
                    <span class="status-chip retry">⚠️ Fallido (Reintentar)</span>
                @else
                    <span class="status-chip pending">○ Pendiente</span>
                @endif
                <span class="attempts-count">{{ $intentos->count() }} {{ $intentos->count() === 1 ? 'intento' : 'intentos' }}</span>
            </div>

            <div class="card-footer-actions mt-3">
                <a href="{{ route('ejercicios.show', $ejercicio) }}" class="button small full-width">
                    {{ $haAcertado ? 'Practicar de nuevo' : 'Comenzar Ejercicio' }} →
                </a>
            </div>
        </div>
    @empty
        <div class="card empty full-width">
            <p>No se encontraron ejercicios en esta categoría.</p>
        </div>
    @endforelse
</div>

<div class="pagination-wrapper mt-4">
    {{ $ejercicios->links() }}
</div>
@endsection
