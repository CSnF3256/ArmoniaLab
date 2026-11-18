# Reconocimiento de acordes por micrófono

## Objetivo

ArmoníaLab permite que el estudiante toque un acorde y reciba retroalimentación sobre las notas esperadas. Esta primera versión reconoce entre 2 y 6 clases de nota configuradas por el docente.

## Configuración administrativa

En el formulario de ejercicios, el docente activa **Reconocimiento de acorde por micrófono** e ingresa las notas separadas por comas:

```text
Do4, Mi4, Sol4
```

Laravel valida en el servidor que:

- existan entre 2 y 6 notas;
- no haya notas repetidas;
- cada nota pertenezca al catálogo admitido;
- cada nota incluya una octava entre 2 y 6;
- se utilicen nombres como `Do4`, `Fa#4` o `Sib3`.

La configuración se guarda en el campo JSON `metadata` del ejercicio:

```json
{
  "audio_captura": true,
  "audio_notas_objetivo": ["Do4", "Mi4", "Sol4"]
}
```

## Flujo técnico

1. El navegador solicita permiso mediante `navigator.mediaDevices.getUserMedia()`.
2. `AudioContext` conecta el micrófono con un `AnalyserNode`.
3. El nivel de entrada se calcula con RMS sobre la señal temporal.
4. Una FFT de 32768 muestras transforma el sonido en energía por frecuencia y mejora la resolución entre notas cercanas.
5. Cada nombre y octava se convierte a una frecuencia musical específica.
6. La aplicación compara las notas detectadas con `audio_notas_objetivo`.
7. Al activar el micrófono se calibra el ruido ambiental durante aproximadamente 1.5 segundos.
8. Cada frecuencia objetivo debe superar el ruido calibrado y formar un pico espectral definido.
9. Se rechazan notas fuertes ajenas dentro del rango de la disposición configurada.
10. El acorde se confirma después de doce lecturas completas consecutivas y se muestra un porcentaje de confianza.

## Privacidad y seguridad

- El audio se procesa en memoria dentro del navegador.
- No se graba ningún archivo.
- No se envía audio al servidor ni se almacena en PostgreSQL.
- El micrófono se libera al detener la captura, cambiar de página o cerrar la pestaña.
- En producción, el navegador exige HTTPS para permitir acceso al micrófono; Render ya ofrece HTTPS.

## Alcance de esta versión

Es un prototipo educativo basado en análisis espectral. El estudiante debe tocar la disposición de notas y octavas configurada por el docente. El resultado depende del micrófono, el ruido, la afinación y el instrumento. No reemplaza un afinador profesional ni un modelo especializado de reconocimiento musical.

Para una lectura estable, el estudiante debe mantener silencio durante la calibración inicial. Si cambia el ruido del ambiente, puede usar el botón **Recalibrar ruido**.

## Archivos principales

- `app/Http/Controllers/Admin/AdminEjercicioController.php`: validación y almacenamiento.
- `resources/views/admin/ejercicios/create.blade.php`: configuración al crear.
- `resources/views/admin/ejercicios/edit.blade.php`: configuración al editar.
- `resources/views/components/audio-captura.blade.php`: captura, FFT y retroalimentación.
- `tests/Feature/AudioCapturaTest.php`: pruebas del servidor y renderizado.
