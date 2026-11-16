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
</div>

@push('scripts')
<script>
(function initAudioCaptura() {
    const startBtn    = document.getElementById('audio-start-btn');
    const stopBtn     = document.getElementById('audio-stop-btn');
    const statusBadge = document.getElementById('audio-device-status');
    const errorBox    = document.getElementById('audio-error-msg');
    const levelBar    = document.getElementById('audio-level-bar');
    const dbLabel     = document.getElementById('audio-db-label');
    const canvas      = document.getElementById('audio-visualizer');

    if (!startBtn || !canvas) return;

    let mediaStream = null;
    let audioContext = null;
    let analyser = null;
    let animationId = null;

    async function solicitarMicrofono() {
        errorBox.style.display = 'none';
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            mostrarError('Tu navegador no soporta la API getUserMedia para captura de audio.');
            return;
        }

        try {
            statusBadge.textContent = '⏳ Solicitando permiso...';
            mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });

            const tracks = mediaStream.getAudioTracks();
            const label = (tracks[0] && tracks[0].label) ? tracks[0].label : 'Micrófono conectado';
            statusBadge.textContent = '🟢 ' + label;
            statusBadge.className = 'audio-device-badge is-active';

            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-flex';

            iniciarVisualizador(mediaStream);
        } catch (err) {
            mostrarError('No se pudo acceder al micrófono: ' + (err.message || 'Permiso denegado.'));
        }
    }

    function iniciarVisualizador(stream) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const source = audioContext.createMediaStreamSource(stream);
        analyser = audioContext.createAnalyser();
        analyser.fftSize = 256;
        source.connect(analyser);

        const bufferLength = analyser.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);
        const ctx = canvas.getContext('2d');

        function renderFrame() {
            animationId = requestAnimationFrame(renderFrame);
            analyser.getByteFrequencyData(dataArray);

            let sum = 0;
            for (let i = 0; i < bufferLength; i++) sum += dataArray[i];
            const avg = sum / bufferLength;
            const pct = Math.min(100, Math.round((avg / 140) * 100));

            if (levelBar) levelBar.style.width = pct + '%';
            if (dbLabel) dbLabel.textContent = pct + '%';

            ctx.fillStyle = '#0f172a';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            const barWidth = (canvas.width / bufferLength) * 2.2;
            let x = 0;
            for (let i = 0; i < bufferLength; i++) {
                const barHeight = (dataArray[i] / 255) * canvas.height;
                ctx.fillStyle = barHeight > canvas.height * 0.7 ? '#f43f5e' : '#818cf8';
                ctx.fillRect(x, canvas.height - barHeight, barWidth, barHeight);
                x += barWidth + 1;
            }
        }
        renderFrame();
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
        statusBadge.textContent = '⚪ Micrófono liberado';
        statusBadge.className = 'audio-device-badge';
        startBtn.style.display = 'inline-flex';
        stopBtn.style.display = 'none';
    }

    function mostrarError(msg) {
        statusBadge.textContent = '🔴 Error de captura';
        statusBadge.className = 'audio-device-badge is-error';
        errorBox.textContent = msg;
        errorBox.style.display = 'block';
    }

    startBtn.addEventListener('click', solicitarMicrofono);
    stopBtn.addEventListener('click', liberarMicrofono);

    // Liberar el micrófono automáticamente al salir del ejercicio
    window.addEventListener('beforeunload', liberarMicrofono);
    window.addEventListener('pagehide', liberarMicrofono);
})();
</script>
@endpush