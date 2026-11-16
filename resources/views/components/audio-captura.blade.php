{{--
    ╔══════════════════════════════════════════════════════════════════╗
    ║   COMPONENTE: audio-captura                                      ║
    ║   Captura de micrófono en tiempo real para ejercicios musicales  ║
    ║   Commits:                                                       ║
    ║     feat(audio): solicitar acceso al microfono                   ║
    ║     feat(audio): mostrar estado del dispositivo de entrada       ║
    ║     feat(audio): visualizar nivel de sonido capturado            ║
    ║     fix(audio): liberar el microfono al salir del ejercicio      ║
    ╚══════════════════════════════════════════════════════════════════╝
--}}

<div id="audio-captura-panel"
     class="mt-6 rounded-xl border border-indigo-200 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-700 p-5"
     data-audio-panel="true">

    {{-- ── Encabezado ── --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span class="text-2xl" aria-hidden="true">🎙️</span>
            <h3 class="font-semibold text-indigo-800 dark:text-indigo-200 text-base">
                Entrada de micrófono
            </h3>
        </div>
        {{-- Badge de estado del dispositivo (commit 2) --}}
        <span id="audio-device-badge"
              class="text-xs px-3 py-1 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-medium">
            Sin dispositivo
        </span>
    </div>

    {{-- ── Mensajes de error / permiso (commit 1) ── --}}
    <div id="audio-error-panel" class="hidden mb-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 p-3">
        <p id="audio-error-msg" class="text-sm text-red-700 dark:text-red-300 font-medium"></p>
    </div>

    {{-- ── Mensaje de bienvenida / solicitud de permiso ── --}}
    <div id="audio-welcome" class="text-center py-4">
        <p class="text-sm text-indigo-700 dark:text-indigo-300 mb-3">
            Este ejercicio puede escuchar tu interpretación en tiempo real.
        </p>
        <button id="audio-start-btn"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <span>🎤</span> Activar micrófono
        </button>
    </div>

    {{-- ── Panel activo (visible tras el permiso) ── --}}
    <div id="audio-active-panel" class="hidden">

        {{-- Estado del dispositivo (commit 2) --}}
        <div class="flex items-center gap-2 mb-3">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-400 animate-pulse" aria-hidden="true"></span>
            <span id="audio-device-name" class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                Micrófono activo
            </span>
            <button id="audio-stop-btn"
                    type="button"
                    class="ml-auto text-xs px-2 py-1 rounded bg-red-100 hover:bg-red-200 dark:bg-red-900/40 dark:hover:bg-red-900/70 text-red-700 dark:text-red-300 transition-colors"
                    title="Detener captura">
                ⏹ Detener
            </button>
        </div>

        {{-- ── Visualizador de nivel de sonido (commit 3) ── --}}
        <div class="relative rounded-lg overflow-hidden bg-gray-900 border border-gray-700" style="height: 90px;">
            <canvas id="audio-visualizer"
                    class="w-full h-full"
                    aria-label="Visualizador de nivel de sonido del micrófono"></canvas>
            <div class="absolute bottom-1 right-2 text-xs text-gray-500 select-none" aria-hidden="true">
                VU · ArmoníaLab
            </div>
        </div>

        {{-- Indicador de volumen compacto --}}
        <div class="mt-2 flex items-center gap-2">
            <span class="text-xs text-gray-500 dark:text-gray-400 w-10">Vol.</span>
            <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                <div id="audio-volume-bar"
                     class="h-full rounded-full bg-gradient-to-r from-green-400 via-yellow-400 to-red-500 transition-all duration-75"
                     style="width: 0%;"
                     role="progressbar"
                     aria-valuenow="0"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
            <span id="audio-volume-value" class="text-xs text-gray-500 dark:text-gray-400 w-8 text-right">0 dB</span>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════
     JAVASCRIPT DEL COMPONENTE (módulo IIFE para aislamiento)
     Commits cubiertos:
       1. feat(audio): solicitar acceso al microfono
       2. feat(audio): mostrar estado del dispositivo de entrada
       3. feat(audio): visualizar nivel de sonido capturado
       4. fix(audio): liberar el microfono al salir del ejercicio
════════════════════════════════════════════════════════════════════ --}}
<script>
(function ArmoniaLabAudio() {
    'use strict';

    // ── Referencias DOM ────────────────────────────────────────────
    const startBtn      = document.getElementById('audio-start-btn');
    const stopBtn       = document.getElementById('audio-stop-btn');
    const welcomeDiv    = document.getElementById('audio-welcome');
    const activePanel   = document.getElementById('audio-active-panel');
    const errorPanel    = document.getElementById('audio-error-panel');
    const errorMsg      = document.getElementById('audio-error-msg');
    const deviceBadge   = document.getElementById('audio-device-badge');
    const deviceName    = document.getElementById('audio-device-name');
    const canvas        = document.getElementById('audio-visualizer');
    const volumeBar     = document.getElementById('audio-volume-bar');
    const volumeValue   = document.getElementById('audio-volume-value');

    // ── Estado interno ─────────────────────────────────────────────
    /** @type {MediaStream|null} */
    let stream          = null;
    /** @type {AudioContext|null} */
    let audioCtx        = null;
    /** @type {AnalyserNode|null} */
    let analyser        = null;
    /** @type {number|null} */
    let rafId           = null;

    // ── Helpers ────────────────────────────────────────────────────

    /**
     * Muestra un mensaje de error descriptivo al usuario.
     * @param {string} mensaje
     */
    function mostrarError(mensaje) {
        errorMsg.textContent = mensaje;
        errorPanel.classList.remove('hidden');
    }

    function ocultarError() {
        errorPanel.classList.add('hidden');
        errorMsg.textContent = '';
    }

    /**
     * Mapea los errores estándar de getUserMedia a mensajes en español.
     * @param {DOMException|Error} err
     * @returns {string}
     */
    function mensajeDeError(err) {
        // commit 1: manejo de excepciones si el usuario deniega el acceso
        switch (err.name) {
            case 'NotAllowedError':
            case 'PermissionDeniedError':
                return '🚫 Permiso denegado. Por favor permite el acceso al micrófono en la configuración de tu navegador.';
            case 'NotFoundError':
            case 'DevicesNotFoundError':
                return '🔌 No se encontró ningún micrófono conectado. Conecta un dispositivo de entrada y vuelve a intentarlo.';
            case 'NotReadableError':
            case 'TrackStartError':
                return '⚠️ El micrófono está en uso por otra aplicación. Ciérrala e inténtalo de nuevo.';
            case 'OverconstrainedError':
            case 'ConstraintNotSatisfiedError':
                return '⚙️ Las restricciones de audio no son compatibles con tu dispositivo.';
            case 'SecurityError':
                return '🔒 Acceso bloqueado por política de seguridad. Asegúrate de usar HTTPS.';
            default:
                return `❌ Error inesperado al acceder al micrófono: ${err.message || err.name}`;
        }
    }

    // ── Commit 2: mostrar estado del dispositivo de entrada ────────

    /**
     * Enumera los dispositivos de audio disponibles y muestra el
     * nombre del micrófono activo en el badge y el label de estado.
     */
    async function mostrarDispositivoEntrada() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const micDevices = devices.filter(d => d.kind === 'audioinput');

            if (micDevices.length === 0) {
                deviceBadge.textContent = 'Sin micrófono';
                deviceName.textContent  = 'No se detectaron micrófonos';
                return;
            }

            // Tomamos el primer dispositivo activo (el que el stream usa)
            // Los labels solo están disponibles después de tener permiso
            const label = micDevices[0].label || `Micrófono (${micDevices.length} disponible${micDevices.length > 1 ? 's' : ''})`;

            deviceBadge.textContent = `🎙️ ${label}`;
            deviceBadge.className   = deviceBadge.className
                .replace('bg-gray-200', 'bg-green-100')
                .replace('text-gray-600', 'text-green-700');
            deviceName.textContent  = label;
        } catch (e) {
            deviceBadge.textContent = 'Estado desconocido';
            console.warn('[ArmoníaLab Audio] No se pudo enumerar dispositivos:', e);
        }
    }

    // ── Commit 3: visualizar nivel de sonido capturado ─────────────

    /**
     * Inicializa el AnalyserNode y lanza el bucle de animación.
     * @param {MediaStream} mediaStream
     */
    function iniciarVisualizador(mediaStream) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        analyser = audioCtx.createAnalyser();
        analyser.fftSize            = 256;
        analyser.smoothingTimeConstant = 0.75;

        const source = audioCtx.createMediaStreamSource(mediaStream);
        source.connect(analyser);

        const bufferLength  = analyser.frequencyBinCount;
        const dataArray     = new Uint8Array(bufferLength);
        const ctx2d         = canvas.getContext('2d');

        /**
         * Bucle de animación: dibuja un espectrograma de barras VU
         * usando requestAnimationFrame.
         */
        function dibujar() {
            rafId = requestAnimationFrame(dibujar);

            // Ajustar resolución al tamaño del canvas
            const W = canvas.offsetWidth;
            const H = canvas.offsetHeight;
            if (canvas.width !== W)  canvas.width  = W;
            if (canvas.height !== H) canvas.height = H;

            analyser.getByteFrequencyData(dataArray);

            // Fondo degradado oscuro
            const bg = ctx2d.createLinearGradient(0, 0, 0, H);
            bg.addColorStop(0, '#0f172a');
            bg.addColorStop(1, '#1e293b');
            ctx2d.fillStyle = bg;
            ctx2d.fillRect(0, 0, W, H);

            // Barras espectrales
            const barWidth = Math.max(2, (W / bufferLength) * 2.5);
            let x = 0;

            for (let i = 0; i < bufferLength; i++) {
                const barHeight = (dataArray[i] / 255) * H;

                // Color según intensidad
                const r = Math.round(100 + (dataArray[i] / 255) * 155);
                const g = Math.round(80  - (dataArray[i] / 255) * 30);
                const b = Math.round(220 - (dataArray[i] / 255) * 100);

                const gradient = ctx2d.createLinearGradient(0, H, 0, H - barHeight);
                gradient.addColorStop(0, `rgba(${r},${g},${b},0.9)`);
                gradient.addColorStop(1, `rgba(${r + 30},${g + 20},255,0.5)`);
                ctx2d.fillStyle = gradient;
                ctx2d.fillRect(x, H - barHeight, barWidth - 1, barHeight);

                x += barWidth;
            }

            // Actualizar barra de volumen compacta (RMS aproximado)
            const rms = Math.round(
                (dataArray.reduce((acc, v) => acc + v, 0) / bufferLength / 255) * 100
            );
            const pct = Math.min(100, rms * 2); // amplificamos visualmente
            volumeBar.style.width           = `${pct}%`;
            volumeBar.setAttribute('aria-valuenow', pct);
            volumeValue.textContent         = rms > 0 ? `-${100 - rms} dB` : '—';
        }

        dibujar();
    }

    // ── Commit 4: liberar el micrófono al salir del ejercicio ──────

    /**
     * Detiene todos los tracks del stream y libera recursos de audio.
     * Se llama tanto desde el botón "Detener" como desde los eventos
     * beforeunload y visibilitychange.
     */
    function liberarMicrofono() {
        // Cancelar animación
        if (rafId !== null) {
            cancelAnimationFrame(rafId);
            rafId = null;
        }

        // Detener todos los tracks (commit 4)
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }

        // Cerrar contexto de audio
        if (audioCtx && audioCtx.state !== 'closed') {
            audioCtx.close().catch(() => {});
            audioCtx = null;
            analyser  = null;
        }

        // Resetear UI
        activePanel.classList.add('hidden');
        welcomeDiv.classList.remove('hidden');
        deviceBadge.textContent = 'Sin dispositivo';
        deviceBadge.className   = deviceBadge.className
            .replace('bg-green-100', 'bg-gray-200')
            .replace('text-green-700', 'text-gray-600');

        // Limpiar canvas
        if (canvas.getContext) {
            const ctx2d = canvas.getContext('2d');
            ctx2d.clearRect(0, 0, canvas.width, canvas.height);
        }
    }

    // Liberar al salir de la página (evitar fugas de recursos)
    window.addEventListener('beforeunload', liberarMicrofono);

    // Liberar cuando la pestaña queda oculta
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && stream) {
            liberarMicrofono();
        }
    });

    // ── Commit 1: solicitar acceso al micrófono ────────────────────

    /**
     * Solicita acceso al micrófono usando getUserMedia.
     * Gestiona los posibles errores con mensajes claros en español.
     */
    async function solicitarMicrofono() {
        ocultarError();
        startBtn.disabled    = true;
        startBtn.textContent = '⏳ Solicitando acceso…';

        // Verificar soporte del navegador
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            mostrarError('🌐 Tu navegador no soporta la captura de audio. Usa Chrome, Firefox o Edge actualizados.');
            startBtn.disabled    = false;
            startBtn.textContent = '🎤 Activar micrófono';
            return;
        }

        try {
            // Solicitar solo audio con constraints básicos para mayor compatibilidad
            stream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    sampleRate:       44100,
                },
                video: false,
            });

            // Permiso concedido ✓
            welcomeDiv.classList.add('hidden');
            activePanel.classList.remove('hidden');

            // commit 2: mostrar estado del dispositivo de entrada
            await mostrarDispositivoEntrada();

            // commit 3: iniciar visualizador de sonido
            iniciarVisualizador(stream);

        } catch (err) {
            // commit 1: manejo de excepciones
            startBtn.disabled    = false;
            startBtn.textContent = '🎤 Activar micrófono';
            mostrarError(mensajeDeError(err));
            console.error('[ArmoníaLab Audio] getUserMedia error:', err.name, err.message);
        }
    }

    // ── Event Listeners ────────────────────────────────────────────
    if (startBtn) startBtn.addEventListener('click', solicitarMicrofono);
    if (stopBtn)  stopBtn.addEventListener('click',  liberarMicrofono);

})();
</script>

