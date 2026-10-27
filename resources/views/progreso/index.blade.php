@extends('layouts.app')

@section('title', 'Mi Progreso por Habilidad y Recomendaciones | ArmoníaLab')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">Analítica de Aprendizaje Personalizada</span>
        <h1>Mi Progreso y Recomendaciones por Habilidad</h1>
        <p class="subtitle">Visualiza tu evolución en teoría musical, diagnóstico de fortalezas y prácticas personalizadas adaptadas a tus resultados.</p>
    </div>
    <div class="actions">
        <a href="{{ route('ejercicios.index') }}" class="button">✍️ Resolver Ejercicios</a>
    </div>
</div>

<!-- Tarjetas de Resumen General -->
<div class="metrics-grid mb-4">
    <div class="card metric-card">
        <span class="metric-icon">🎯</span>
        <div class="metric-info">
            <span class="metric-label">Precisión Global</span>
            <span class="metric-value">{{ $diagnostico['porcentaje_global'] }}%</span>
        </div>
    </div>

    <div class="card metric-card">
        <span class="metric-icon">📝</span>
        <div class="metric-info">
            <span class="metric-label">Intentos Totales</span>
            <span class="metric-value">{{ $diagnostico['total_intentos'] }}</span>
        </div>
    </div>

    <div class="card metric-card">
        <span class="metric-icon">✓</span>
        <div class="metric-info">
            <span class="metric-label">Aciertos Registrados</span>
            <span class="metric-value">{{ $diagnostico['total_correctos'] }}</span>
        </div>
    </div>

    <div class="card metric-card">
        <span class="metric-icon">🌟</span>
        <div class="metric-info">
            <span class="metric-label">Habilidades Dominadas</span>
            <span class="metric-value">{{ count($diagnostico['habilidades_dominadas']) }} / {{ count($diagnostico['habilidades']) }}</span>
        </div>
    </div>
</div>

<!-- SECCIÓN DE RECOMENDACIÓN PERSONALIZADA INTELIGENTE (Recomendación del Docente) -->
<div class="card recommendation-section mb-4">
    <div class="rec-banner-header">
        <div>
            <span class="rec-badge">🧠 Diagnóstico Adaptativo</span>
            <h2>Prácticas Personalizadas Recomendadas</h2>
            <p class="text-muted">{{ $diagnostico['mensaje_diagnostico'] }}</p>
        </div>
    </div>

    @if(!empty($diagnostico['recomendaciones']))
        <div class="rec-items-grid mt-3">
            @foreach($diagnostico['recomendaciones'] as $rec)
                <div class="rec-chip-card highlight">
                    <div class="rec-chip-content">
                        <span class="badge-skill">{{ $rec['ejercicio']->habilidad }}</span>
                        <h4 class="mt-2">{{ $rec['ejercicio']->pregunta }}</h4>
                        <p class="rec-reason">📌 {{ $rec['motivo'] }}</p>
                    </div>
                    <div class="rec-chip-action">
                        <a href="{{ route('ejercicios.show', $rec['ejercicio']) }}" class="button small">
                            Practicar Ejercicio →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty p-3">
            <p>¡No tienes habilidades críticas en este momento! Puedes seguir practicando con el catálogo completo.</p>
        </div>
    @endif
</div>

<!-- Progreso por Habilidad Armónica -->
<div class="card mb-4">
    <h2>Desglose de Competencias Musicales</h2>
    <p class="subtitle mb-4">El sistema evalúa tus respuestas en el Back-End y clasifica cada habilidad para orientar tu estudio.</p>

    <div class="skills-grid">
        @forelse($diagnostico['habilidades'] as $hab)
            <div class="skill-card">
                <div class="skill-card-top">
                    <div>
                        <h3>{{ $hab['nombre'] }}</h3>
                        <span class="skill-attempts">{{ $hab['totales'] }} intentos ({{ $hab['correctos'] }} correctos, {{ $hab['fallidos'] }} fallidos)</span>
                    </div>
                    <span class="status-badge {{ str_replace(' ', '-', strtolower($hab['estado'])) }}">
                        {{ $hab['estado'] }}
                    </span>
                </div>

                <div class="progress-bar-container mt-3">
                    <div class="progress-bar-fill {{ str_replace(' ', '-', strtolower($hab['estado'])) }}" style="width: {{ $hab['porcentaje'] }}%;"></div>
                </div>

                <div class="skill-card-bottom mt-2">
                    <span class="percentage-text">{{ $hab['porcentaje'] }}% de aciertos</span>
                    <a href="{{ route('ejercicios.index', ['habilidad' => $hab['nombre']]) }}" class="link-small">
                        Ver ejercicios de esta habilidad →
                    </a>
                </div>
            </div>
        @empty
            <div class="empty full-width">
                <p>Aún no has generado datos suficientes para calcular tu progreso por habilidad.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Historial Completo de Intentos -->
<div class="card">
    <h2>Registro Histórico de Intentos</h2>
    <p class="subtitle mb-3">Auditoría completa de todas las respuestas enviadas al servidor y evaluadas automáticamente.</p>

    @if($historialIntentos->isEmpty())
        <div class="empty p-4">
            <p>No tienes intentos registrados todavía.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Habilidad</th>
                        <th>Pregunta</th>
                        <th>Tu Respuesta</th>
                        <th>Evaluación</th>
                        <th>Feedback</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historialIntentos as $intento)
                        <tr>
                            <td>{{ $intento->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge-skill small">{{ $intento->habilidad ?: 'General' }}</span></td>
                            <td>{{ Str::limit($intento->ejercicio?->pregunta ?? 'Ejercicio', 40) }}</td>
                            <td><code>{{ $intento->respuesta_usuario }}</code></td>
                            <td>
                                @if($intento->es_correcta)
                                    <span class="status-chip success">✓ Correcto</span>
                                @else
                                    <span class="status-chip retry">✗ Incorrecto</span>
                                @endif
                            </td>
                            <td class="text-sm">{{ $intento->explicacion_resultado }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper mt-3">
            {{ $historialIntentos->links() }}
        </div>
    @endif
</div>
@endsection
