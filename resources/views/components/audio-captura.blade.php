<style>
.audio-captura-card {
    background: #ffffff;
    border: 1px solid #e0e7ff;
    border-radius: 14px;
    padding: 1.5rem;
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.08);
}
.audio-captura-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.audio-title-group {
    display: flex;
    align-items: center;
    gap: .75rem;
}
.audio-mic-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #eef2ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
}
.audio-device-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .35rem .85rem;
    border-radius: 999px;
    font-size: .82rem;
    font-weight: 600;
    background: #f3f4f6;
    color: #4b5563;
}
.audio-device-badge.is-active {
    background: #dcfce7;
    color: #15803d;
}
.audio-device-badge.is-error {
    background: #fee2e2;
    color: #b91c1c;
}
.audio-controls-row {
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
    margin-top: .75rem;
}
.btn-mic-primary {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff;
    border: none;
    padding: .65rem 1.25rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: .95rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    transition: transform .15s ease, box-shadow .15s ease;
}
.btn-mic-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
}
.btn-mic-stop {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
    padding: .6rem 1.15rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: .9rem;
    cursor: pointer;
    display: none;
    align-items: center;
    gap: .4rem;
    transition: background .15s ease;
}
.btn-mic-stop:hover {
    background: #fecaca;
}
.audio-meter-wrapper {
    margin-top: 1.25rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem;
}
.audio-meter-header {
    display: flex;
    justify-content: space-between;
    font-size: .85rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: .5rem;
}
.audio-meter-bar-bg {
    width: 100%;
    height: 12px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: .75rem;
}
.audio-meter-bar-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #22c55e 0%, #eab308 70%, #ef4444 100%);
    border-radius: 999px;
    transition: width .08s linear;
}
.audio-canvas-box {
    width: 100%;
    height: 70px;
    background: #0f172a;
    border-radius: 8px;
    display: block;
}
.audio-error-alert {
    display: none;
    margin-top: .75rem;
    padding: .75rem 1rem;
    border-radius: 8px;
    background: #fef2f2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
    font-size: .9rem;
}
.audio-recognition {
    margin-top: 1rem;
    padding: 1rem;
    border: 1px solid #ddd6fe;
    border-radius: 10px;
    background: #faf5ff;
}
.audio-recognition-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: .75rem;
}
.audio-recognition-item {
    padding: .75rem;
    border-radius: 8px;
    background: #fff;
    border: 1px solid #ede9fe;
}
.audio-recognition-label {
    display: block;
    color: #6b7280;
    font-size: .78rem;
    font-weight: 700;
    margin-bottom: .3rem;
    text-transform: uppercase;
}
.audio-recognition-result {
    margin-top: .75rem;
    padding: .75rem 1rem;
    border-radius: 8px;
    background: #eef2ff;
    color: #3730a3;
    font-weight: 700;
}
.audio-recognition-result.is-correct {
    background: #dcfce7;
    color: #166534;
}
.audio-recognition-result.is-calibrating {
    background: #fff7ed;
    color: #9a3412;
}
.audio-confidence {
    font-variant-numeric: tabular-nums;
}
.btn-audio-calibrate {
    display: none;
    border: 1px solid #c4b5fd;
    background: #f5f3ff;
    color: #5b21b6;
    padding: .6rem 1rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
}
</style>

<div class="card audio-captura-card mt-4" id="audio-captura-panel">
    <div class="audio-captura-top">
        <div class="audio-title-group">
            <div class="audio-mic-icon">🎙️</div>
            <div>
                <h3 style="margin:0;font-size:1.1rem;color:#1e1b4b;">Entrada de micrófono en tiempo real</h3>
                <p style="margin:0;font-size:.88rem;color:#6b7280;">Comprueba tu interpretación instrumental o vocal mientras resuelves el ejercicio.</p>
            </div>
        </div>
        <span class="audio-device-badge" id="audio-device-status">
            ⚪ Sin dispositivo activo
        </span>
    </div>

    <div class="audio-controls-row">
        <button type="button" class="btn-mic-primary" id="audio-start-btn">
            🎤 Activar micrófono
        </button>
        <button type="button" class="btn-mic-stop" id="audio-stop-btn">
            ⏹ Detener captura
        </button>
        <button type="button" class="btn-audio-calibrate" id="audio-calibrate-btn">
            🎚️ Recalibrar ruido
        </button>
    </div>

    <div class="audio-error-alert" id="audio-error-msg" role="alert"></div>

    <div class="audio-meter-wrapper">
        <div class="audio-meter-header">
            <span>Nivel de entrada (VU · ArmoníaLab)</span>
            <span id="audio-db-label">0%</span>
        </div>
        <div class="audio-meter-bar-bg">
            <div class="audio-meter-bar-fill" id="audio-level-bar"></div>
        </div>
        <canvas id="audio-visualizer" class="audio-canvas-box" width="600" height="70"></canvas>
    </div>

    @if(!empty($ejercicio->metadata['audio_notas_objetivo']))
    <div class="audio-recognition" id="audio-recognition-panel">
        <div class="audio-recognition-grid">
            <div class="audio-recognition-item">
                <span class="audio-recognition-label">Acorde objetivo</span>
                <strong id="audio-target-notes">{{ implode(' · ', $ejercicio->metadata['audio_notas_objetivo'] ?? []) }}</strong>
            </div>
            <div class="audio-recognition-item">
                <span class="audio-recognition-label">Notas detectadas</span>
                <strong id="audio-detected-notes">—</strong>
            </div>
            <div class="audio-recognition-item">
                <span class="audio-recognition-label">Confianza</span>
                <strong class="audio-confidence" id="audio-confidence">0%</strong>
            </div>
        </div>
        <div class="audio-recognition-result" id="audio-recognition-result" aria-live="polite">
            Activa el micrófono y toca el acorde de forma sostenida.
        </div>
        <p style="margin:.6rem 0 0;color:#6b7280;font-size:.8rem;">
            El análisis se realiza en este navegador. ArmoníaLab no graba ni envía tu audio al servidor.
        </p>
    </div>
    @endif
</div>

@push('scripts')
<script>
(function initAudioCaptura() {
    const startBtn    = document.getElementById('audio-start-btn');
    const stopBtn     = document.getElementById('audio-stop-btn');
    const calibrateBtn = document.getElementById('audio-calibrate-btn');
    const statusBadge = document.getElementById('audio-device-status');
    const errorBox    = document.getElementById('audio-error-msg');
    const levelBar    = document.getElementById('audio-level-bar');
    const dbLabel     = document.getElementById('audio-db-label');
    const canvas      = document.getElementById('audio-visualizer');
    const detectedBox = document.getElementById('audio-detected-notes');
    const resultBox   = document.getElementById('audio-recognition-result');
    const confidenceBox = document.getElementById('audio-confidence');
    const targetNotes = @json(array_values($ejercicio->metadata['audio_notas_objetivo'] ?? []));

    if (!startBtn || !canvas) return;

    let mediaStream = null;
    let audioContext = null;
    let analyser = null;
    let animationId = null;
    let framesCorrectos = 0;
    let calibrando = false;
    let muestrasCalibracion = 0;
    let ruidoRms = 0;
    let ruidoPorNota = {};
    const totalMuestrasCalibracion = 15;

    const pitchClasses = {
        'Do': 0, 'Do#': 1, 'Reb': 1, 'Re': 2, 'Re#': 3, 'Mib': 3,
        'Mi': 4, 'Fa': 5, 'Fa#': 6, 'Solb': 6, 'Sol': 7,
        'Sol#': 8, 'Lab': 8, 'La': 9, 'La#': 10, 'Sib': 10, 'Si': 11
    };

    async function solicitarMicrofono() {
        errorBox.style.display = 'none';
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            mostrarError('Tu navegador no soporta la API getUserMedia para captura de audio.');
            return;
        }

        try {
            statusBadge.textContent = '⏳ Solicitando permiso...';
            mediaStream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: false,
                    noiseSuppression: false,
                    autoGainControl: false
                },
                video: false
            });

            const tracks = mediaStream.getAudioTracks();
            const label = (tracks[0] && tracks[0].label) ? tracks[0].label : 'Micrófono conectado';
            statusBadge.textContent = '🟢 ' + label;
            statusBadge.className = 'audio-device-badge is-active';

            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-flex';
            if (calibrateBtn) calibrateBtn.style.display = 'inline-flex';

            iniciarVisualizador(mediaStream);
        } catch (err) {
            mostrarError('No se pudo acceder al micrófono: ' + (err.message || 'Permiso denegado.'));
        }
    }

    function iniciarVisualizador(stream) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const source = audioContext.createMediaStreamSource(stream);
        analyser = audioContext.createAnalyser();
        analyser.fftSize = 32768;
        analyser.smoothingTimeConstant = 0.72;
        source.connect(analyser);

        const bufferLength = analyser.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);
        const timeData = new Float32Array(analyser.fftSize);
        const ctx = canvas.getContext('2d');
        let frame = 0;
        let rmsActual = 0;

        iniciarCalibracion();

        function renderFrame() {
            animationId = requestAnimationFrame(renderFrame);
            analyser.getByteFrequencyData(dataArray);

            frame++;
            if (frame % 3 === 0) {
                analyser.getFloatTimeDomainData(timeData);
                let sumaCuadrados = 0;
                for (let i = 0; i < timeData.length; i++) sumaCuadrados += timeData[i] * timeData[i];
                rmsActual = Math.sqrt(sumaCuadrados / timeData.length);
                const pct = Math.min(100, Math.round(rmsActual * 500));
                if (levelBar) levelBar.style.width = pct + '%';
                if (dbLabel) dbLabel.textContent = pct + '%';
            }
            if (frame % 6 === 0) analizarAcorde(dataArray, rmsActual);

            ctx.fillStyle = '#0f172a';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            const visualBins = Math.min(240, bufferLength);
            const barWidth = canvas.width / visualBins;
            let x = 0;
            for (let i = 0; i < visualBins; i++) {
                const barHeight = (dataArray[i] / 255) * canvas.height;
                ctx.fillStyle = barHeight > canvas.height * 0.7 ? '#f43f5e' : '#818cf8';
                ctx.fillRect(x, canvas.height - barHeight, barWidth, barHeight);
                x += barWidth + 1;
            }
        }
        renderFrame();
    }

    function iniciarCalibracion() {
        calibrando = true;
        muestrasCalibracion = 0;
        ruidoRms = 0;
        ruidoPorNota = Object.fromEntries(targetNotes.map(nota => [nota, 0]));
        framesCorrectos = 0;
        if (confidenceBox) confidenceBox.textContent = '0%';
        if (detectedBox) detectedBox.textContent = 'Calibrando…';
        if (resultBox) {
            resultBox.textContent = 'Mantén silencio durante la calibración del ruido ambiental…';
            resultBox.classList.remove('is-correct');
            resultBox.classList.add('is-calibrating');
        }
    }

    function analizarAcorde(dataArray, rms) {
        if (!targetNotes.length || !analyser || !audioContext) return;

        if (calibrando) {
            muestrasCalibracion++;
            ruidoRms += rms;
            targetNotes.forEach(nota => {
                ruidoPorNota[nota] += medirNota(nota, dataArray).energia;
            });

            const progreso = Math.round((muestrasCalibracion / totalMuestrasCalibracion) * 100);
            if (confidenceBox) confidenceBox.textContent = progreso + '% calibrado';

            if (muestrasCalibracion >= totalMuestrasCalibracion) {
                ruidoRms /= totalMuestrasCalibracion;
                targetNotes.forEach(nota => {
                    ruidoPorNota[nota] /= totalMuestrasCalibracion;
                });
                calibrando = false;
                detectedBox.textContent = '—';
                confidenceBox.textContent = '0%';
                resultBox.textContent = 'Calibración lista. Ahora toca el acorde de forma sostenida.';
                resultBox.classList.remove('is-calibrating');
            }
            return;
        }

        const umbralRms = Math.max(0.015, ruidoRms * 3.5);
        if (rms < umbralRms) {
            framesCorrectos = 0;
            detectedBox.textContent = 'Esperando sonido…';
            confidenceBox.textContent = '0%';
            resultBox.textContent = 'La señal está por debajo del ruido ambiental.';
            resultBox.classList.remove('is-correct');
            return;
        }

        let maximo = 0;
        for (let i = 0; i < dataArray.length; i++) maximo = Math.max(maximo, dataArray[i]);

        const lecturas = targetNotes.map(nota => {
            const lectura = medirNota(nota, dataArray);
            const umbral = Math.max(40, (ruidoPorNota[nota] || 0) * 2.5 + 15, maximo * 0.18);
            const presente = lectura.energia >= umbral && lectura.prominencia >= 1.15;
            const confianza = presente
                ? Math.min(100, Math.round((lectura.energia / Math.max(umbral, 1)) * 60))
                : Math.min(59, Math.round((lectura.energia / Math.max(umbral, 1)) * 60));
            return { nota, ...lectura, presente, confianza };
        });

        const detectadas = lecturas.filter(lectura => lectura.presente).map(lectura => lectura.nota);
        const confianza = lecturas.length ? Math.min(...lecturas.map(lectura => lectura.confianza)) : 0;
        const notaAjena = detectarNotaAjena(dataArray, lecturas);
        detectedBox.textContent = detectadas.length ? detectadas.join(' · ') : 'Ninguna nota objetivo';
        confidenceBox.textContent = confianza + '%';

        if (detectadas.length === targetNotes.length && !notaAjena) {
            framesCorrectos++;
        } else {
            framesCorrectos = Math.max(0, framesCorrectos - 1);
        }

        if (framesCorrectos >= 12) {
            resultBox.textContent = '✅ ¡Acorde correcto! Se detectaron todas las notas objetivo.';
            resultBox.classList.add('is-correct');
        } else {
            const faltantes = targetNotes.filter(nota => !detectadas.includes(nota));
            resultBox.textContent = notaAjena
                ? 'Se detectó una nota adicional (' + notaAjena + '). Toca únicamente la disposición objetivo.'
                : faltantes.length
                    ? 'Sigue intentando. Faltan por detectar: ' + faltantes.join(', ') + '.'
                    : 'Mantén el acorde un momento para confirmar la lectura.';
            resultBox.classList.remove('is-correct');
        }
    }

    function especificacionDeNota(nota) {
        const coincidencia = /^(.+)([2-6])$/.exec(nota);
        if (!coincidencia) return null;

        const pitchClass = pitchClasses[coincidencia[1]];
        const octava = Number(coincidencia[2]);
        if (pitchClass === undefined) return null;

        return { nota, midi: (octava + 1) * 12 + pitchClass };
    }

    function medirNota(nota, dataArray) {
        const especificacion = especificacionDeNota(nota);
        return especificacion ? medirMidi(especificacion.midi, dataArray) : { energia: 0, prominencia: 0 };
    }

    function medirMidi(midi, dataArray) {
        const frecuencia = 440 * Math.pow(2, (midi - 69) / 12);
        const tolerancia = Math.pow(2, 35 / 1200);
        const binMin = Math.max(1, Math.floor((frecuencia / tolerancia) * analyser.fftSize / audioContext.sampleRate));
        const binMax = Math.min(dataArray.length - 2, Math.ceil((frecuencia * tolerancia) * analyser.fftSize / audioContext.sampleRate));
        let energia = 0;

        for (let bin = binMin; bin <= binMax; bin++) {
            energia = Math.max(energia, dataArray[bin] || 0);
        }

        let vecindad = 0;
        let muestras = 0;
        for (let bin = Math.max(1, binMin - 8); bin <= Math.min(dataArray.length - 1, binMax + 8); bin++) {
            if (bin >= binMin && bin <= binMax) continue;
            vecindad += dataArray[bin] || 0;
            muestras++;
        }

        const promedioVecindad = muestras ? vecindad / muestras : 0;
        return { energia, prominencia: energia / Math.max(1, promedioVecindad) };
    }

    function detectarNotaAjena(dataArray, lecturasObjetivo) {
        const especificaciones = targetNotes.map(especificacionDeNota).filter(Boolean);
        if (!especificaciones.length) return null;

        const midiObjetivo = new Set(especificaciones.map(item => item.midi));
        const energiaObjetivo = Math.max(...lecturasObjetivo.map(item => item.energia), 0);
        const midiMin = Math.min(...especificaciones.map(item => item.midi));
        const midiMax = Math.max(...especificaciones.map(item => item.midi));
        const nombres = ['Do', 'Do#', 'Re', 'Re#', 'Mi', 'Fa', 'Fa#', 'Sol', 'Sol#', 'La', 'La#', 'Si'];

        for (let midi = midiMin; midi <= midiMax; midi++) {
            if (midiObjetivo.has(midi)) continue;
            const lectura = medirMidi(midi, dataArray);
            if (lectura.energia >= Math.max(55, energiaObjetivo * 0.78) && lectura.prominencia >= 1.25) {
                return nombres[midi % 12] + (Math.floor(midi / 12) - 1);
            }
        }

        return null;
    }

    function liberarMicrofono() {
        if (animationId) {
            cancelAnimationFrame(animationId);
            animationId = null;
        }
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }
        if (audioContext && audioContext.state !== 'closed') {
            audioContext.close().catch(() => {});
            audioContext = null;
        }
        if (levelBar) levelBar.style.width = '0%';
        if (dbLabel) dbLabel.textContent = '0%';
        if (detectedBox) detectedBox.textContent = '—';
        if (confidenceBox) confidenceBox.textContent = '0%';
        if (resultBox) {
            resultBox.textContent = 'Activa el micrófono y toca el acorde de forma sostenida.';
            resultBox.classList.remove('is-correct', 'is-calibrating');
        }
        framesCorrectos = 0;
        calibrando = false;
        statusBadge.textContent = '⚪ Micrófono liberado';
        statusBadge.className = 'audio-device-badge';
        startBtn.style.display = 'inline-flex';
        stopBtn.style.display = 'none';
        if (calibrateBtn) calibrateBtn.style.display = 'none';
    }

    function mostrarError(msg) {
        statusBadge.textContent = '🔴 Error de captura';
        statusBadge.className = 'audio-device-badge is-error';
        errorBox.textContent = msg;
        errorBox.style.display = 'block';
    }

    startBtn.addEventListener('click', solicitarMicrofono);
    stopBtn.addEventListener('click', liberarMicrofono);
    if (calibrateBtn) calibrateBtn.addEventListener('click', iniciarCalibracion);

    // Liberar el micrófono automáticamente al salir del ejercicio
    window.addEventListener('beforeunload', liberarMicrofono);
    window.addEventListener('pagehide', liberarMicrofono);
})();
</script>
@endpush
