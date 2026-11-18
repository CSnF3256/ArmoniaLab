@extends('layouts.app')

@section('title', 'Ejercicios Interactivos con Autocorrección | ArmoníaLab')

@section('content')
<style>
/* ── Badges de Modalidad y Audio en Tarjetas ────────────────────── */
.exercise-badges-row {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .5rem;
    margin-bottom: .25rem;
}
.badge-modalidad {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .22rem .65rem;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 700;
    background: #eef2ff;
    color: #4338ca;
    border: 1px solid #c7d2fe;
}
.badge-modalidad.is-interactive {
    background: linear-gradient(135deg, #ede9fe, #e0e7ff);
    color: #5b21b6;
    border-color: #a78bfa;
}
.badge-mic-active {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    padding: .22rem .55rem;
    border-radius: 999px;
    font-size: .73rem;
    font-weight: 700;
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #86efac;
}

/* ── Paginación Limpia ArmoníaLab (Sin SVGs gigantes) ───────────── */
.armonia-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}
.armonia-pagination-info {
    font-size: .88rem;
    color: #6b7280;
}
.armonia-pagination-links {
    display: flex;
    align-items: center;
    gap: .4rem;
    flex-wrap: wrap;
}
.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 .75rem;
    border-radius: 8px;
    font-size: .88rem;
    font-weight: 600;
    text-decoration: none;
    background: #f9fafb;
    color: #374151;
    border: 1px solid #d1d5db;
    transition: all .15s ease;
}
.page-btn:hover:not(.disabled):not(.active) {
    background: #eef2ff;
    color: #4f46e5;
    border-color: #a5b4fc;
}
.page-btn.active {
    background: #6366f1;
    color: #ffffff;
    border-color: #6366f1;
    box-shadow: 0 2px 6px rgba(99, 102, 241, 0.3);
}
.page-btn.disabled {
    opacity: .45;
    cursor: not-allowed;
    pointer-events: none;
}
</style>

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
@php
    $modalidadesCatalogo = \App\Models\Ejercicio::modalidades();
    $iconosModalidad = [
        'clasica'               => '📋',
        'completar_secuencia'   => '🎵',
        'seleccionar_intervalo' => '📏',
        'verdadero_falso'       => '⚖️',
        'ordenar_notas'         => '🔢',
        'detectar_error'        => '🔍',
        'construccion_visual'   => '🎼',
        'progresion_funcional'  => '🧩',
        'sincronia_ritmica'     => '⏱️',
    ];
@endphp

<div class="exercises-grid">
    @forelse($ejercicios as $ejercicio)
        @php
            $intentos = $intentosUsuario->get($ejercicio->id, collect());
            $ultimoIntento = $intentos->first();
            $haAcertado = $intentos->contains('es_correcta', true);
            $modKey = $ejercicio->modalidad ?? 'clasica';
            $modNombre = $modalidadesCatalogo[$modKey] ?? ucfirst(str_replace('_', ' ', $modKey));
            $modIcono = $iconosModalidad[$modKey] ?? '🎓';
            $esInteractivo = in_array($modKey, ['construccion_visual', 'progresion_funcional', 'sincronia_ritmica']);
            $tieneMic = !empty($ejercicio->metadata['audio_captura']);
        @endphp

        <div class="card exercise-card {{ $haAcertado ? 'passed' : ($ultimoIntento ? 'failed' : '') }}">
            <div class="exercise-card-header">
                <span class="badge-skill">{{ $ejercicio->habilidad }}</span>
                <span class="badge-difficulty {{ strtolower($ejercicio->dificultad) }}">{{ $ejercicio->dificultad }}</span>
            </div>

            <div class="exercise-badges-row">
                <span class="badge-modalidad {{ $esInteractivo ? 'is-interactive' : '' }}" title="Tipo de ejercicio">
                    {{ $modIcono }} {{ $modNombre }}
                </span>
                @if($tieneMic)
                    <span class="badge-mic-active" title="Incluye captura de micrófono en tiempo real">
                        🎙️ Micrófono
                    </span>
                @endif
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

@if($ejercicios->hasPages())
    <nav class="armonia-pagination mt-4" role="navigation" aria-label="Paginación de ejercicios">
        <div class="armonia-pagination-info">
            Mostrando <strong>{{ $ejercicios->firstItem() }}</strong> a <strong>{{ $ejercicios->lastItem() }}</strong> de <strong>{{ $ejercicios->total() }}</strong> ejercicios
        </div>
        <div class="armonia-pagination-links">
            @if($ejercicios->onFirstPage())
                <span class="page-btn disabled">← Anterior</span>
            @else
                <a href="{{ $ejercicios->previousPageUrl() }}" class="page-btn" rel="prev">← Anterior</a>
            @endif

            @foreach($ejercicios->getUrlRange(1, $ejercicios->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="page-btn {{ $page === $ejercicios->currentPage() ? 'active' : '' }}">
                    {{ $page }}
                </a>
            @endforeach

            @if($ejercicios->hasMorePages())
                <a href="{{ $ejercicios->nextPageUrl() }}" class="page-btn" rel="next">Siguiente →</a>
            @else
                <span class="page-btn disabled">Siguiente →</span>
            @endif
        </div>
    </nav>
@endif
@endsection