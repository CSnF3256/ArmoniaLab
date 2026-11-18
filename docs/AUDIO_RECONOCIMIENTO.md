# Reconocimiento de acordes por micrófono

## Objetivo

ArmoníaLab permite que el estudiante toque un acorde y reciba retroalimentación sobre las notas esperadas. Esta primera versión reconoce entre 2 y 6 clases de nota configuradas por el docente.

## Configuración administrativa

En el formulario de ejercicios, el docente activa **Reconocimiento de acorde por micrófono** e ingresa las notas separadas por comas:

```text
Do, Mi, Sol
```

Laravel valida en el servidor que:

- existan entre 2 y 6 notas;
- no haya notas repetidas;
- cada nota pertenezca al catálogo admitido;
- se utilicen nombres como `Do`, `Fa#` o `Sib`.

La configuración se guarda en el campo JSON `metadata` del ejercicio:

```json
{
  "audio_captura": true,
  "audio_notas_objetivo": ["Do", "Mi", "Sol"]
}
```

## Flujo técnico

1. El navegador solicita permiso mediante `navigator.mediaDevices.getUserMedia()`.
2. `AudioContext` conecta el micrófono con un `AnalyserNode`.
3. Una FFT de 8192 muestras transforma el sonido en energía por frecuencia.
4. Las frecuencias de las octavas musicales se relacionan con sus clases de nota.
5. La aplicación compara las notas detectadas con `audio_notas_objetivo`.
6. El acorde se confirma después de seis lecturas completas consecutivas para reducir resultados inestables.

## Privacidad y seguridad

- El audio se procesa en memoria dentro del navegador.
- No se graba ningún archivo.
- No se envía audio al servidor ni se almacena en PostgreSQL.
- El micrófono se libera al detener la captura, cambiar de página o cerrar la pestaña.
- En producción, el navegador exige HTTPS para permitir acceso al micrófono; Render ya ofrece HTTPS.

## Alcance de esta versión

Es un prototipo educativo basado en análisis espectral. El resultado depende del micrófono, el ruido, la afinación y el instrumento. No reemplaza un afinador profesional ni un modelo especializado de reconocimiento musical.

## Archivos principales

- `app/Http/Controllers/Admin/AdminEjercicioController.php`: validación y almacenamiento.
- `resources/views/admin/ejercicios/create.blade.php`: configuración al crear.
- `resources/views/admin/ejercicios/edit.blade.php`: configuración al editar.
- `resources/views/components/audio-captura.blade.php`: captura, FFT y retroalimentación.
- `tests/Feature/AudioCapturaTest.php`: pruebas del servidor y renderizado.
