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

            <div class="options-container">
                <label class="options-label">Selecciona tu respuesta:</label>

                @php
                    $opciones = $ejercicio->datos_pregunta['opciones'] ?? [];
                @endphp

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
