@extends('layouts.app')

@section('title', 'Constructor de Acordes - Core MVC | ArmoníaLab')

@section('content')
<div class="core-header">
    <div class="core-badge">Módulo Core MVC</div>
    <h1>Constructor y Visualizador de Acordes</h1>
    <p class="subtitle">Selecciona una nota fundamental y un tipo de acorde para calcular sus intervalos armónicos y visualizar las teclas en el teclado virtual.</p>
</div>

<div class="core-grid">
    <!-- Formulario de Selección (Figura 5 del documento) -->
    <div class="card core-form-card">
        <h2>Parámetros del Acorde</h2>
        
        <form method="GET" action="{{ route('acordes.index') }}" class="chord-form">
            <div class="chord-form-fields">
                <div>
                    <label for="fundamental">Nota Fundamental</label>
                    <select id="fundamental" name="fundamental" required>
                        @foreach($notasCromaticas as $nota)
                            <option value="{{ $nota }}" @selected($fundamentalSeleccionada === $nota)>
                                {{ $nota }} ({{ \App\Services\AcordeService::NOTAS_ANGLO[$loop->index] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tipo_id">Tipo de Acorde</label>
                    <select id="tipo_id" name="tipo_id" required>
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo->id }}" @selected($tipoSeleccionado == $tipo->id)>
                                {{ $tipo->nombre }} ({{ $tipo->simbolo ?: 'M' }}) — {{ $tipo->categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="actions mt-4">
                <button type="submit" class="button full-width">⚡ Calcular y Visualizar Acorde</button>
            </div>
        </form>

        <div class="chord-quick-chips mt-4">
            <span class="chips-label">Atajos rápidos:</span>
            <a href="{{ route('acordes.index', ['fundamental' => 'Do', 'tipo_id' => $tipos->firstWhere('nombre', 'Mayor')?->id ?? 1]) }}" class="chip">Do Mayor (C)</a>
            <a href="{{ route('acordes.index', ['fundamental' => 'La', 'tipo_id' => $tipos->firstWhere('nombre', 'Menor')?->id ?? 2]) }}" class="chip">La Menor (Am)</a>
            <a href="{{ route('acordes.index', ['fundamental' => 'Sol', 'tipo_id' => $tipos->firstWhere('nombre', 'Séptima dominante')?->id ?? 3]) }}" class="chip">Sol 7 (G7)</a>
            <a href="{{ route('acordes.index', ['fundamental' => 'Fa', 'tipo_id' => $tipos->firstWhere('nombre', 'Mayor')?->id ?? 1]) }}" class="chip">Fa Mayor (F)</a>
        </div>
    </div>

    <!-- Resultados del Cálculo (Figura 6 del documento) -->
    @if($resultado)
        <div class="card core-result-card">
            <div class="result-header">
                <div>
                    <span class="eyebrow">Resultado Armónico</span>
                    <h2 class="chord-title">{{ $resultado['nombre_completo'] }}</h2>
                    <span class="cipher-badge">{{ $resultado['cifrado_americano'] }}</span>
                </div>

                <div class="audio-controls">
                    <button type="button" id="btn-play-chord" class="button audio-btn" onclick="playChordSound()">
                        🔊 Escuchar Acorde
                    </button>
                    
                    @auth
                        <form method="POST" action="{{ route('acordes.guardar') }}" class="inline-form">
                            @csrf
                            <input type="hidden" name="fundamental" value="{{ $resultado['fundamental'] }}">
                            <input type="hidden" name="tipo_id" value="{{ $resultado['tipo_id'] }}">
                            <button type="submit" class="button secondary small">⭐ Guardar en Biblioteca</button>
                        </form>
                    @endauth
                </div>
            </div>

            <!-- Resumen de Notas -->
            <div class="notes-display">
                <div class="notes-chips">
                    @foreach($resultado['desglose'] as $item)
                        <div class="note-pill {{ $loop->first ? 'root' : '' }}">
                            <span class="note-name">{{ $item['nota'] }}</span>
                            <span class="note-anglo">{{ $item['nota_anglo'] }}</span>
                            <span class="note-degree">{{ $item['nombre_intervalo'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Teclado Virtual Interactivo (Figura 5 del documento) -->
            <div class="keyboard-container">
                <div class="keyboard-legend">
                    <span class="legend-item"><span class="legend-dot root-dot"></span> Fundamental</span>
                    <span class="legend-item"><span class="legend-dot active-dot"></span> Notas del Acorde</span>
                    <span class="legend-hint">Haz click en las teclas para escuchar su tono individual</span>
                </div>

                <div class="piano-keyboard" id="piano-keyboard">
                    {{-- Renderizamos 2 octavas (24 semitonos cromáticos) --}}
                    @php
                        $notasOctava = ['Do', 'Do#', 'Re', 'Re#', 'Mi', 'Fa', 'Fa#', 'Sol', 'Sol#', 'La', 'La#', 'Si'];
                        $esNegra = [false, true, false, true, false, false, true, false, true, false, true, false];
                    @endphp

                    <div class="keyboard-octave">
                        @foreach($notasOctava as $idx => $nota)
                            @php
                                $esActiva = in_array($idx, $resultado['teclas_activas']);
                                $esFundamental = ($resultado['fundamental'] === $nota);
                                $negra = $esNegra[$idx];
                            @endphp
                            <div class="key {{ $negra ? 'black-key' : 'white-key' }} {{ $esActiva ? 'active' : '' }} {{ $esFundamental ? 'root-key' : '' }}"
                                 data-note="{{ $nota }}"
                                 data-pitch="{{ 261.63 * pow(2, $idx / 12) }}"
                                 onclick="playSingleNote({{ 261.63 * pow(2, $idx / 12) }}, '{{ $nota }}')">
                                <span class="key-label">{{ $nota }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Explicación Matemática del Core MVC (Criterio de Validación Docente) -->
            <div class="calculation-breakdown mt-4">
                <h3>📐 Verificación Matemática del Cálculo</h3>
                <p class="math-intro">
                    El Core de ArmoníaLab calcula las notas mediante aritmética modular sobre las 12 clases cromáticas:
                    <code>Posición = (Posición_Fundamental + Semitonos_Intervalo) mod 12</code>
                </p>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Intervalo</th>
                                <th>Semitonos</th>
                                <th>Fórmula Verificable</th>
                                <th>Nota Resultante</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resultado['pasos_calculo'] as $idx => $paso)
                                <tr>
                                    <td><strong>{{ $resultado['desglose'][$idx]['nombre_intervalo'] }}</strong></td>
                                    <td><span class="badge-semitone">+{{ $paso['semitono'] }} st</span></td>
                                    <td><code>{{ $paso['formula'] }}</code></td>
                                    <td><strong class="note-highlight">{{ $paso['resultado'] }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif($errorCalculo)
        <div class="card error-card">
            <h3>Error al calcular el acorde</h3>
            <p>{{ $errorCalculo }}</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Web Audio API para síntesis polifónica en tiempo real
let audioCtx = null;

function getAudioContext() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
    return audioCtx;
}

// Frecuencias base para cada semitono (Do4 = 261.63 Hz)
const FRECUENCIAS = {
    'Do': 261.63,
    'Do#': 277.18,
    'Re': 293.66,
    'Re#': 311.13,
    'Mi': 329.63,
    'Fa': 349.23,
    'Fa#': 369.99,
    'Sol': 392.00,
    'Sol#': 415.30,
    'La': 440.00,
    'La#': 466.16,
    'Si': 493.88
};

function playSingleNote(freq, noteName) {
    const ctx = getAudioContext();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();

    osc.type = 'triangle';
    osc.frequency.setValueAtTime(freq, ctx.currentTime);

    // Envolvente de volumen (ADSR simplificado para sonido cálido tipo piano/órgano)
    gain.gain.setValueAtTime(0, ctx.currentTime);
    gain.gain.linearRampToValueAtTime(0.35, ctx.currentTime + 0.05);
    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 1.2);

    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.start();
    osc.stop(ctx.currentTime + 1.2);
}

function playChordSound() {
    @if($resultado)
        const ctx = getAudioContext();
        const notas = @json($resultado['notas']);
        
        notas.forEach((nota, index) => {
            const freq = FRECUENCIAS[nota] || 261.63;
            // Arpegio ultra-sutil (25ms entre notas para sonido natural)
            const delay = index * 0.035;

            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'triangle';
            osc.frequency.setValueAtTime(freq, ctx.currentTime + delay);

            gain.gain.setValueAtTime(0, ctx.currentTime + delay);
            gain.gain.linearRampToValueAtTime(0.25 / notas.length, ctx.currentTime + delay + 0.05);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + delay + 2.0);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(ctx.currentTime + delay);
            osc.stop(ctx.currentTime + delay + 2.0);
        });
    @endif
}
</script>
@endpush
