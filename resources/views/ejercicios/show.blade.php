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

            @elseif($modalidad === 'construccion_visual')
                {{-- ══════════════════════════════════════════════════════════════
                     MODALIDAD: Construcción Visual (Drag & Drop)
                     El estudiante arrastra notas desde el banco al pentagrama.
                     Al enviar, el campo oculto #respuesta lleva las notas
                     colocadas en el pentagrama separadas por " - ".
                ══════════════════════════════════════════════════════════════ --}}
                <input type="hidden" name="respuesta" id="respuesta-dragdrop" value="">
                <div class="dragdrop-exercise">
                    <p class="options-label">Arrastra las notas al pentagrama para construir el acorde. El orden importa (de grave a agudo).</p>

                    {{-- Banco de notas disponibles --}}
                    <div class="nota-bank" id="nota-bank">
                        @foreach($opciones as $nota)
                            <div class="nota-token draggable" draggable="true" data-nota="{{ $nota }}" id="token-{{ $loop->index }}">
                                🎵 {{ $nota }}
                            </div>
                        @endforeach
                    </div>

                    {{-- Zona objetivo: pentagrama simplificado --}}
                    <div class="pentagrama-zona mt-3" id="pentagrama-zona">
                        <div class="pentagrama-label">🎼 Pentagrama — suelta las notas aquí en orden</div>
                        <div class="pentagrama-slots" id="pentagrama-slots">
                            @php $cantSlots = count($opciones) > 3 ? count($opciones) : 4; @endphp
                            @for($s = 0; $s < $cantSlots; $s++)
                                <div class="pentagrama-slot droptarget" data-slot="{{ $s }}">
                                    <span class="slot-number">{{ $s + 1 }}</span>
                                </div>
                            @endfor
                        </div>
                        <button type="button" class="button small secondary mt-2" id="btn-limpiar-pent">🗑 Limpiar</button>
                    </div>

                    {{-- Validación visual de la respuesta armada --}}
                    <div class="dragdrop-preview mt-3" id="dragdrop-preview" style="display:none;">
                        <strong>Tu respuesta:</strong> <span id="preview-text" style="font-family:monospace;"></span>
                    </div>

                    @if(count($opciones) === 0)
                        <p class="text-sm text-muted">Este ejercicio no tiene notas configuradas.</p>
                    @endif
                </div>

            @elseif($modalidad === 'progresion_funcional')
                {{-- ══════════════════════════════════════════════════════════════
                     MODALIDAD: Progresiones Funcionales
                     El estudiante ordena bloques de acordes arrastrándolos
                     para formar la progresión correcta (I-IV-V-I, ii-V-I, etc.).
                     Al enviar, el campo oculto lleva los acordes unidos por " > ".
                ══════════════════════════════════════════════════════════════ --}}
                <input type="hidden" name="respuesta" id="respuesta-progresion" value="">
                <div class="progresion-exercise">
                    <p class="options-label">Arrastra los bloques de acordes para ordenar la progresión armónica correcta.</p>

                    @php
                        $acorBloques = $opciones; // ej: ["Am", "F", "C", "G"]
                        $shuffled    = collect($acorBloques)->shuffle()->values()->all();
                        $reglasTxt   = $ejercicio->datos_pregunta['reglas'] ?? null;
                        $contextoProg = $ejercicio->datos_pregunta['contexto'] ?? null;
                    @endphp

                    @if($contextoProg)
                        <div class="progresion-context-box mb-3" style="background:#f0fdf4;border-left:4px solid #22c55e;padding:.75rem 1rem;border-radius:6px;">
                            🎼 <strong>Contexto armónico:</strong> {{ $contextoProg }}
                        </div>
                    @endif
                    @if($reglasTxt)
                        <div class="progresion-rules-box mb-3" style="background:#eff6ff;border-left:4px solid #3b82f6;padding:.75rem 1rem;border-radius:6px;">
                            📐 <strong>Regla armónica:</strong> {{ $reglasTxt }}
                        </div>
                    @endif

                    {{-- Zona de ordenamiento --}}
                    <div class="progresion-board" id="progresion-board">
                        @foreach($shuffled as $idx => $acorde)
                            <div class="progresion-block chord-block" draggable="true"
                                 data-acorde="{{ $acorde }}" id="block-{{ $idx }}">
                                <span class="chord-roman">{{ $acorde }}</span>
                                <span class="chord-drag-hint">⠿</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Flecha visual --}}
                    <div class="progresion-arrows mt-2" id="progresion-arrows" aria-hidden="true"></div>

                    {{-- Preview de la progresión armada --}}
                    <div class="progresion-preview mt-3" id="progresion-preview" style="display:none;">
                        <strong>Progresión armada:</strong>
                        <span id="progresion-text" style="font-family:monospace;font-size:1.1rem;"></span>
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
<style>
/* ── Drag & Drop: Construcción Visual ──────────────────────────────── */
.dragdrop-exercise { padding: 1rem 0; }
.nota-bank {
    display: flex; flex-wrap: wrap; gap: .6rem;
    padding: 1rem; border: 2px dashed #c7d2fe;
    border-radius: 10px; background: #f5f3ff; min-height: 60px;
}
.nota-token {
    padding: .45rem .9rem; background: #6366f1; color: #fff;
    border-radius: 8px; cursor: grab; font-weight: 600;
    user-select: none; transition: transform .15s, box-shadow .15s;
    box-shadow: 0 2px 6px rgba(99,102,241,.3);
}
.nota-token:active { cursor: grabbing; }
.nota-token.dragging { opacity: .4; transform: scale(.95); }
.nota-token.placed { opacity: .3; cursor: not-allowed; pointer-events: none; }
.pentagrama-zona { background: #fff7ed; border: 2px solid #fed7aa; border-radius: 10px; padding: 1rem; }
.pentagrama-label { font-size: .85rem; color: #9a3412; margin-bottom: .6rem; font-weight: 600; }
.pentagrama-slots { display: flex; gap: .5rem; flex-wrap: wrap; }
.pentagrama-slot {
    width: 80px; min-height: 60px; border: 2px dashed #fb923c;
    border-radius: 8px; display: flex; align-items: center; justify-content: center;
    flex-direction: column; gap: .2rem; background: #fff; position: relative;
    transition: background .15s, border-color .15s;
}
.pentagrama-slot.drag-over { background: #fef3c7; border-color: #f59e0b; }
.pentagrama-slot .slot-number { font-size: .7rem; color: #d97706; position: absolute; top: 4px; left: 6px; }
.pentagrama-slot .slot-nota { font-size: .9rem; font-weight: 700; color: #1e1b4b; }
.dragdrop-preview { padding: .6rem 1rem; background: #ecfdf5; border-radius: 8px; color: #065f46; }

/* ── Progresiones Funcionales ─────────────────────────────────────── */
.progresion-exercise { padding: 1rem 0; }
.progresion-board {
    display: flex; flex-wrap: wrap; gap: .75rem;
    min-height: 90px; padding: 1rem;
    border: 2px dashed #a5b4fc; border-radius: 10px; background: #eef2ff;
    align-items: center;
}
.progresion-block {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    width: 80px; height: 80px; border-radius: 12px;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    color: #fff; font-weight: 800; font-size: 1.1rem;
    cursor: grab; user-select: none; box-shadow: 0 4px 12px rgba(99,102,241,.35);
    transition: transform .15s, box-shadow .15s; position: relative;
}
.progresion-block:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(99,102,241,.45); }
.progresion-block.dragging { opacity: .35; transform: scale(.92); }
.progresion-block.drag-over-block { outline: 3px solid #f59e0b; outline-offset: 2px; }
.chord-drag-hint { font-size: .65rem; opacity: .7; margin-top: .15rem; letter-spacing: 2px; }
.progresion-arrows { display: flex; gap: .5rem; align-items: center; min-height: 24px; color: #6366f1; font-size: 1.3rem; flex-wrap: wrap; }
.progresion-preview { padding: .6rem 1rem; background: #eff6ff; border-radius: 8px; color: #1e3a8a; }
</style>
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

/* ══════════════════════════════════════════════════════════════════════
   DRAG & DROP — Modalidad: construccion_visual
   ══════════════════════════════════════════════════════════════════════ */
(function initDragDrop() {
    const bank    = document.getElementById('nota-bank');
    const slots   = document.querySelectorAll('.pentagrama-slot');
    const hiddenR = document.getElementById('respuesta-dragdrop');
    const preview = document.getElementById('dragdrop-preview');
    const prevTxt = document.getElementById('preview-text');
    const btnLimp = document.getElementById('btn-limpiar-pent');
    const form    = document.getElementById('form-ejercicio');

    if (!bank || !hiddenR) return; // modalidad no activa

    let draggingToken = null;

    // ── Iniciar arrastre desde el banco ──────────────────────────────
    bank.querySelectorAll('.nota-token').forEach(token => {
        token.addEventListener('dragstart', e => {
            draggingToken = token;
            token.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', token.dataset.nota);
        });
        token.addEventListener('dragend', () => token.classList.remove('dragging'));
    });

    // ── Slots del pentagrama ─────────────────────────────────────────
    slots.forEach(slot => {
        slot.addEventListener('dragover', e => {
            e.preventDefault();
            slot.classList.add('drag-over');
        });
        slot.addEventListener('dragleave', () => slot.classList.remove('drag-over'));
        slot.addEventListener('drop', e => {
            e.preventDefault();
            slot.classList.remove('drag-over');
            if (!draggingToken || draggingToken.classList.contains('placed')) return;
            if (slot.dataset.filled) return; // ya ocupado

            const nota = draggingToken.dataset.nota;
            slot.dataset.filled = nota;
            slot.innerHTML = `<span class="slot-number">${parseInt(slot.dataset.slot) + 1}</span>
                              <span class="slot-nota">🎵 ${nota}</span>`;
            draggingToken.classList.add('placed');
            draggingToken.setAttribute('draggable', 'false');
            actualizarRespuesta();
        });

        // Permitir devolver nota al banco con doble clic
        slot.addEventListener('dblclick', () => {
            const nota = slot.dataset.filled;
            if (!nota) return;
            const tokenId = [...bank.querySelectorAll('.nota-token')]
                .find(t => t.dataset.nota === nota && t.classList.contains('placed'));
            if (tokenId) {
                tokenId.classList.remove('placed');
                tokenId.setAttribute('draggable', 'true');
            }
            slot.dataset.filled = '';
            slot.innerHTML = `<span class="slot-number">${parseInt(slot.dataset.slot) + 1}</span>`;
            actualizarRespuesta();
        });
    });

    // ── Limpiar todo ─────────────────────────────────────────────────
    if (btnLimp) {
        btnLimp.addEventListener('click', () => {
            slots.forEach(slot => {
                slot.dataset.filled = '';
                slot.innerHTML = `<span class="slot-number">${parseInt(slot.dataset.slot) + 1}</span>`;
            });
            bank.querySelectorAll('.nota-token').forEach(t => {
                t.classList.remove('placed');
                t.setAttribute('draggable', 'true');
            });
            actualizarRespuesta();
        });
    }

    // ── Actualizar campo oculto + preview ────────────────────────────
    function actualizarRespuesta() {
        const notas = [...slots].map(s => s.dataset.filled || '').filter(Boolean);
        const str   = notas.join(' - ');
        hiddenR.value = str;
        if (prevTxt) prevTxt.textContent = str || '(vacío)';
        if (preview) preview.style.display = notas.length ? 'block' : 'none';
    }

    // ── Validación antes de enviar ────────────────────────────────────
    if (form) {
        form.addEventListener('submit', e => {
            if (!hiddenR.value.trim()) {
                e.preventDefault();
                alert('Por favor coloca al menos una nota en el pentagrama antes de enviar.');
            }
        });
    }
})();

/* ══════════════════════════════════════════════════════════════════════
   PROGRESIONES FUNCIONALES — Modalidad: progresion_funcional
   Los bloques se reordenan arrastrándolos dentro del tablero.
   ══════════════════════════════════════════════════════════════════════ */
(function initProgresion() {
    const board   = document.getElementById('progresion-board');
    const hiddenP = document.getElementById('respuesta-progresion');
    const preview = document.getElementById('progresion-preview');
    const prevTxt = document.getElementById('progresion-text');
    const arrows  = document.getElementById('progresion-arrows');
    const form    = document.getElementById('form-ejercicio');

    if (!board || !hiddenP) return; // modalidad no activa

    let draggingBlock = null;

    board.querySelectorAll('.progresion-block').forEach(blk => {
        blk.addEventListener('dragstart', e => {
            draggingBlock = blk;
            blk.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        blk.addEventListener('dragend', () => blk.classList.remove('dragging'));

        blk.addEventListener('dragover', e => {
            e.preventDefault();
            if (blk !== draggingBlock) blk.classList.add('drag-over-block');
        });
        blk.addEventListener('dragleave', () => blk.classList.remove('drag-over-block'));
        blk.addEventListener('drop', e => {
            e.preventDefault();
            blk.classList.remove('drag-over-block');
            if (!draggingBlock || draggingBlock === blk) return;
            // Intercambiar posiciones en el DOM
            const afterBlk = blk.nextSibling;
            if (afterBlk === draggingBlock) {
                board.insertBefore(draggingBlock, blk);
            } else {
                board.insertBefore(draggingBlock, blk);
            }
            actualizarProgresion();
        });
    });

    function actualizarProgresion() {
        const acordes = [...board.querySelectorAll('.progresion-block')]
            .map(b => b.dataset.acorde);
        const str = acordes.join(' > ');
        hiddenP.value = str;
        if (prevTxt) prevTxt.textContent = str;
        if (preview) preview.style.display = acordes.length ? 'block' : 'none';
        // Renderizar flechas de flujo
        if (arrows) {
            arrows.innerHTML = acordes.map((a, i) =>
                i < acordes.length - 1 ? `<span>${a}</span><span>→</span>` : `<span>${a}</span>`
            ).join('');
        }
    }

    // Inicializar el estado visual al cargar
    actualizarProgresion();

    // Validación al enviar
    if (form) {
        form.addEventListener('submit', e => {
            if (!hiddenP.value.trim()) {
                e.preventDefault();
                alert('Por favor ordena los acordes de la progresión antes de enviar.');
            }
        });
    }
})();
</script>
@endpush
