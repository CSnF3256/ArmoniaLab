# Audio Captura — ArmoníaLab

> Rama: `feat/audio-captura` | Revisora: **Camila**  
> PR: `feat(audio): incorporar entrada desde el micrófono`

## Descripción

Este módulo incorpora la captura de audio desde el micrófono del usuario
directamente en los ejercicios musicales interactivos de ArmoníaLab.
El objetivo es permitir que el estudiante interprete un acorde o melodía
en su instrumento real y reciba retroalimentación visual en tiempo real,
complementando los ejercicios de teoría con la práctica auditiva.

---

## Commits atómicos

| # | Commit | Descripción |
|---|--------|-------------|
| 1 | `feat(audio): solicitar acceso al microfono` | `navigator.mediaDevices.getUserMedia` con manejo exhaustivo de errores en español |
| 2 | `feat(audio): mostrar estado del dispositivo de entrada` | `enumerateDevices()` para listar y mostrar el nombre del micrófono activo |
| 3 | `feat(audio): visualizar nivel de sonido capturado` | Espectrograma en canvas usando `AnalyserNode` + barra de volumen VU |
| 4 | `fix(audio): liberar el microfono al salir del ejercicio` | `stream.getTracks().forEach(track => track.stop())` en `beforeunload` y `visibilitychange` |
| 5 | `test(audio): cubrir permisos y errores de captura` | 12 Feature Tests en `AudioCapturaTest.php` |

---

## Arquitectura

### Activación por metadata

El panel de audio se muestra **condicionalmente** en `show.blade.php`:

```blade
@if(!empty($ejercicio->metadata['audio_captura']))
    @include('components.audio-captura')
@endif
```

Para habilitar el audio en un ejercicio, simplemente agrega al campo `metadata`:

```json
{
  "audio_captura": true,
  "pista": "Toca el acorde en tu instrumento"
}
```

### Archivos involucrados

| Archivo | Rol |
|---------|-----|
| `resources/views/components/audio-captura.blade.php` | Componente UI + JS del micrófono |
| `resources/views/ejercicios/show.blade.php` | Punto de inclusión condicional |
| `tests/Feature/AudioCapturaTest.php` | 12 tests de Feature |
| `docs/AUDIO_CAPTURA.md` | Esta documentación |

---

## Flujo del componente

```
Usuario ve el ejercicio
        │
        ▼
  metadata.audio_captura = true?
        │
       SÍ → Muestra panel "Activar micrófono"
        │
        ▼
  Usuario hace clic en "Activar micrófono"
        │
        ▼
  getUserMedia({ audio: true })
        │
   ┌────┴────┐
  Error?    Éxito
   │          │
   ▼          ▼
Mensaje    - enumerateDevices() → muestra nombre del mic
de error   - AnalyserNode → canvas espectrograma
           - barra VU en tiempo real
           - beforeunload → track.stop()
```

---

## Errores manejados (Commit 1)

| Error DOM | Mensaje al usuario |
|-----------|-------------------|
| `NotAllowedError` / `PermissionDeniedError` | Permiso denegado — instrucciones para permitir |
| `NotFoundError` / `DevicesNotFoundError` | No se encontró ningún micrófono |
| `NotReadableError` / `TrackStartError` | Micrófono en uso por otra app |
| `OverconstrainedError` | Restricciones incompatibles con el dispositivo |
| `SecurityError` | Bloqueado por política de seguridad (HTTPS) |
| Sin soporte del navegador | Mensaje de compatibilidad |

---

## Tests (Commit 5)

Archivo: `tests/Feature/AudioCapturaTest.php`

| Test | Qué valida |
|------|-----------|
| `ejercicio_con_audio_captura_renderiza_sin_error` | HTTP 200 en show con audio |
| `vista_incluye_panel_audio_cuando_metadata_activa` | Presencia de IDs del panel en HTML |
| `vista_no_incluye_panel_audio_sin_metadata` | Ausencia del panel cuando no aplica |
| `responder_acepta_tiempo_segundos_como_en_ejercicios_con_audio` | No crash al enviar tiempo |
| `responder_respuesta_correcta_con_audio_captura` | Session `resultado_exito` |
| `responder_respuesta_incorrecta_con_audio_captura` | Session `resultado_error` |
| `metadata_audio_captura_persiste_correctamente` | BD guarda y lee el metadata |
| `admin_puede_crear_ejercicio_con_metadata_audio_captura` | Sin errores de validación en admin |
| `modalidades_clasicas_no_afectadas_por_cambios_audio` | Regresión |
| `usuario_no_autenticado_redirigido_al_intentar_ver_ejercicio_con_audio` | Auth guard |
| `responder_rechaza_respuesta_vacia` | Validación de campo requerido |
| `ejercicio_con_audio_captura_false_no_muestra_panel` | `audio_captura: false` → sin panel |

---

## Consideraciones de seguridad y privacidad

- El micrófono **nunca envía audio al servidor** — todo el procesamiento es local en el navegador.
- El stream se libera inmediatamente al salir del ejercicio (`beforeunload`).
- No se almacena ningún fragmento de audio en la base de datos.
- El usuario debe conceder permiso explícito en cada sesión.

---

## Pull Request

**Título:** `feat(audio): incorporar entrada desde el micrófono`  
**Rama base:** `main`  
**Rama fuente:** `feat/audio-captura`  
**Revisora:** Camila  

### Checklist
- [x] Solicitud de permisos con manejo de errores
- [x] Detección y visualización del dispositivo de entrada
- [x] Visualizador en tiempo real (espectrograma + barra VU)
- [x] Liberación de recursos al salir
- [x] Tests de Feature (12 casos)
- [x] Documentación
- [x] Sin cambios a la lógica existente

