@extends('layouts.app')

@section('title', 'Resolver Ejercicio: ' . $ejercicio->habilidad . ' | ArmoníaLab')

@section('content')
<div class="exercise-solve-container">
    <div class="breadcrumb mb-3">
        <a href="{{ route('ejercicios.index') }}">← Volver a Ejercicios</a>
        <span>/</span>
        <span>{{ $ejercicio->habilidad }}</span>
    </div>

    <div class="card exercise-solve-card">
        <div class="exercise-header">
            <div>
                <span class="badge-skill">{{ $ejercicio->habilidad }}</span>
                <span class="badge-difficulty {{ strtolower($ejercicio->dificultad) }}">{{ $ejercicio->dificultad }}</span>
                @php $modalidadLabel = \App\Models\Ejercicio::modalidades()[$ejercicio->modalidad ?? 'clasica'] ?? 'Clásica'; @endphp
                <span class="badge-skill" style="background:#e0e7ff;color:#3730a3;margin-left:.25rem;" title="Modalidad de presentación">
                    🎓 {{ $modalidadLabel }}
                </span>
            </div>
            <div class="exercise-points">
                <span>Valor: <strong>{{ $ejercicio->puntos }} Puntos</strong></span>
            </div>
        </div>

        <h1 class="exercise-main-title">{{ $ejercicio->pregunta }}</h1>
        <p class="exercise-context">Lección vinculada: <strong>{{ $ejercicio->leccion?->titulo ?? 'Fundamentos' }}</strong> (Curso: {{ $ejercicio->leccion?->course?->title ?? 'Armonía' }})</p>

        <!-- Formulario con Corrección en Back-End -->
        <form method="POST" action="{{ route('ejercicios.responder', $ejercicio) }}" class="exercise-form mt-4" id="form-ejercicio">
            @csrf
            <input type="hidden" name="tiempo_segundos" id="tiempo_segundos" value="0">

            @php
                $modalidad = $ejercicio->modalidad ?? 'clasica';
                $opciones  = $ejercicio->datos_pregunta['opciones'] ?? [];
                $pista     = $ejercicio->metadata['pista'] ?? null;
                $tiempoLim = $ejercicio->metadata['tiempo_limite'] ?? 0;
            @endphp

            {{-- Pista contextual (si el admin la configuró) --}}
            @if($pista)
                <div class="exercise-hint-box mb-3" style="background:#fef9c3;border-left:4px solid #ca8a04;padding:.75rem 1rem;border-radius:6px;">
                    💡 <strong>Pista:</strong> {{ $pista }}
                </div>
            @endif

            {{-- Contador regresivo (si hay tiempo límite configurado) --}}
            @if($tiempoLim > 0)
                <div class="exercise-timer mb-3" id="timer-box" style="font-weight:600;color:#dc2626;">
                    ⏱ Tiempo restante: <span id="timer-display">{{ $tiempoLim }}</span>s
                </div>
            @endif

            {{-- ===== RENDERIZADO SEGÚN MODALIDAD ===== --}}

            @if($modalidad === 'verdadero_falso')
                {{-- Modalidad: Verdadero o Falso --}}
                <div class="options-container">
                    <label class="options-label">¿La afirmación es verdadera o falsa?</label>
                    <div class="options-grid" style="grid-template-columns: repeat(2, 1fr);">
                        @foreach(['Verdadero', 'Falso'] as $idx => $val)
                            <label class="option-card" for="opcion_{{ $idx }}" style="font-size:1.1rem;justify-content:center;">
                                <input type="radio" id="opcion_{{ $idx }}" name="respuesta" value="{{ $val }}" required>
                                <span class="option-indicator">{{ $val === 'Verdadero' ? '✓' : '✗' }}</span>
                                <span class="option-text">{{ $val }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

            @elseif($modalidad === 'ordenar_notas')
                {{-- Modalidad: Ordenar notas (el estudiante escribe el orden correcto) --}}
                <div class="options-container">
                    <label class="options-label">Escribe las notas en el orden correcto (separadas por " - "):</label>
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem;">
                        @foreach($opciones as $nota)
                            <span class="badge-skill" style="cursor:default;">{{ $nota }}</span>
                        @endforeach
                    </div>
                    <input type="text" name="respuesta" class="form-control" required
                           placeholder="Ej: Do - Mi - Sol"
                           style="width:100%;padding:.6rem 1rem;border:1px solid #d1d5db;border-radius:8px;font-size:1rem;">
                    <p class="field-help text-sm text-muted mt-1">Separa las notas con " - " (espacio, guión, espacio).</p>
                </div>

            @elseif($modalidad === 'completar_secuencia')
                {{-- Modalidad: Completar secuencia — elige la nota que falta --}}
                <div class="options-container">
                    <label class="options-label">Selecciona la nota que completa la secuencia:</label>
                    <div class="options-grid">
                        @foreach($opciones as $opcion)
                            <label class="option-card" for="opcion_{{ $loop->index }}">
                                <input type="radio" id="opcion_{{ $loop->index }}" name="respuesta" value="{{ $opcion }}" required>
                                <span class="option-indicator">{{ chr(65 + $loop->index) }}</span>
                                <span class="option-text">🎵 {{ $opcion }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

            @elseif($modalidad === 'seleccionar_intervalo')
                {{-- Modalidad: Seleccionar intervalo — botones con semitonos --}}
                <div class="options-container">
                    <label class="options-label">Selecciona el intervalo correcto en semitonos:</label>
                    <div class="options-grid" style="grid-template-columns: repeat(4, 1fr);">
                        @foreach($opciones as $opcion)
                            <label class="option-card" for="opcion_{{ $loop->index }}" style="text-align:center;flex-direction:column;gap:.25rem;">
                                <input type="radio" id="opcion_{{ $loop->index }}" name="respuesta" value="{{ $opcion }}" required>
                                <span class="option-indicator" style="font-size:1.5rem;">{{ $opcion }}</span>
                                <span class="option-text text-sm">st</span>
                            </label>
                        @endforeach
                    </div>
                </div>

            @elseif($modalidad === 'detectar_error')
                {{-- Modalidad: Detectar la nota errónea --}}
                <div class="options-container">
                    <label class="options-label">¿Cuál de estas notas <strong>NO</strong> pertenece al acorde?</label>
                    <div class="options-grid">
                        @foreach($opciones as $opcion)
                            <label class="option-card" for="opcion_{{ $loop->index }}" style="border-color:#fca5a5;">
                                <input type="radio" id="opcion_{{ $loop->index }}" name="respuesta" value="{{ $opcion }}" required>
                                <span class="option-indicator" style="background:#fef2f2;color:#dc2626;">{{ chr(65 + $loop->index) }}</span>
                                <span class="option-text">{{ $opcion }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

            @else
                {{-- Modalidad por defecto: clasica (opción múltiple estándar) --}}
                <div class="options-container">
                    <label class="options-label">Selecciona tu respuesta:</label>
                    <div class="options-grid">
                        @foreach($opciones as $opcion)
                            <label class="option-card" for="opcion_{{ $loop->index }}">
                                <input type="radio" id="opcion_{{ $loop->index }}" name="respuesta" value="{{ $opcion }}" required>
                                <span class="option-indicator">{{ chr(65 + $loop->index) }}</span>
                                <span class="option-text">{{ $opcion }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="form-actions mt-4">
                <button type="submit" class="button full-width large">
                    ✓ Enviar Respuesta para Corrección en Servidor
                </button>
            </div>
        </form>

        <!-- Teclado de Apoyo Interactivo para el Estudiante -->
        <div class="helper-keyboard-box mt-4">
            <details>
                <summary>🎹 Desplegar teclado virtual de apoyo para comprobar notas y sonidos</summary>
                <div class="helper-keyboard mt-3">
                    <p class="text-sm text-muted">Haz click en las teclas para escuchar su afinación y ayudarte a deducir el acorde.</p>
                    <div class="piano-keyboard mini">
                        @php
                            $notasOctava = ['Do', 'Do#', 'Re', 'Re#', 'Mi', 'Fa', 'Fa#', 'Sol', 'Sol#', 'La', 'La#', 'Si'];
                            $esNegra = [false, true, false, true, false, false, true, false, true, false, true, false];
                        @endphp
                        <div class="keyboard-octave">
                            @foreach($notasOctava as $idx => $n)
                                <div class="key {{ $esNegra[$idx] ? 'black-key' : 'white-key' }}"
                                     onclick="playNoteSound({{ 261.63 * pow(2, $idx / 12) }})">
                                    <span class="key-label">{{ $n }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </details>
        </div>
    </div>

    <!-- Historial de Intentos Previos en este Ejercicio -->
    @if($ultimosIntentos->isNotEmpty())
        <div class="card mt-4">
            <h3>Tus Intentos Previos en este Ejercicio</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tu Respuesta</th>
                            <th>Resultado</th>
                            <th>Explicación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimosIntentos as $intento)
                            <tr>
                                <td>{{ $intento->created_at->format('d/m/Y H:i') }}</td>
                                <td><code>{{ $intento->respuesta_usuario }}</code></td>
                                <td>
                                    @if($intento->es_correcta)
                                        <span class="badge success">Correcto (+{{ $intento->puntaje_obtenido }} pts)</span>
                                    @else
                                        <span class="badge danger">Incorrecto</span>
                                    @endif
                                </td>
                                <td>{{ $intento->explicacion_resultado }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Contador de tiempo de resolución en segundos
let segundos = 0;
const inputTiempo = document.getElementById('tiempo_segundos');
setInterval(() => {
    segundos++;
    if (inputTiempo) inputTiempo.value = segundos;
}, 1000);

// Temporizador regresivo (si hay tiempo límite configurado)
const timerBox = document.getElementById('timer-box');
const timerDisplay = document.getElementById('timer-display');
if (timerBox && timerDisplay) {
    let tiempoRestante = parseInt(timerDisplay.textContent, 10);
    const countdownInterval = setInterval(() => {
        tiempoRestante--;
        timerDisplay.textContent = tiempoRestante;
        if (tiempoRestante <= 10) {
            timerBox.style.color = '#dc2626';
        }
        if (tiempoRestante <= 0) {
            clearInterval(countdownInterval);
            document.getElementById('form-ejercicio').submit();
        }
    }, 1000);
}

// Web Audio API para el teclado de apoyo
let audioCtx = null;
function playNoteSound(freq) {
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (audioCtx.state === 'suspended') audioCtx.resume();

    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();

    osc.type = 'triangle';
    osc.frequency.setValueAtTime(freq, audioCtx.currentTime);

    gain.gain.setValueAtTime(0, audioCtx.currentTime);
    gain.gain.linearRampToValueAtTime(0.3, audioCtx.currentTime + 0.04);
    gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 1.0);

    osc.connect(gain);
    gain.connect(audioCtx.destination);

    osc.start();
    osc.stop(audioCtx.currentTime + 1.0);
}
</script>
@endpush
