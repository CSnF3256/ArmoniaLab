# Avance: Audio y Seguridad en ArmoníaLab

## 1. Objetivo del Avance
Documentar e integrar de forma segura las capacidades de reconocimiento de audio en tiempo real mediante el navegador y robustecer las capas de seguridad, autenticación y despliegue en producción con PostgreSQL (Neon) y Render.

## 2. Arquitectura MVC Involucrada
- **Modelos (`app/Models/`)**: Gestión de ejercicios, roles y relaciones con restricciones de validación.
- **Controladores (`app/Http/Controllers/`)**: Lógica de negocio, control de intentos de acceso y gestión de flujos de profesor/estudiante.
- **Vistas (`resources/views/`)**: Interfaces en Blade con componentes interactivos para la visualización del espectro y captura de audio.

## 3. Flujo de Captura y Reconocimiento de Audio
1. El docente configura las notas objetivo especificando la disposición exacta con octava (ej. `Do4`, `Mi4`, `Sol4`).
2. El sistema valida en el Back-End que el arreglo contenga de 2 a 6 notas válidas.
3. El estudiante abre el ejercicio y el navegador solicita permisos para el micrófono vía `getUserMedia`.
4. Se calcula el volumen instantáneo mediante **RMS** y se procesa el espectro de frecuencias usando **FFT (Fast Fourier Transform)** a través de la API de Web Audio.
5. El sistema detecta las frecuencias dominantes, las mapea al formato de notas con octava y las compara con el acorde objetivo.
6. Se muestra retroalimentación visual inmediata al usuario en tiempo real.

Diagrama de secuencia del flujo:

~~~mermaid
graph TD
    A[Docente configura notas objetivo] -->|Laravel valida en Back-End| B[(PostgreSQL guarda metadata)]
    B --> C[Estudiante abre el ejercicio]
    C --> D[Navegador solicita micrófono]
    D --> E[Web Audio analiza frecuencias FFT y RMS]
    E --> F[Se detectan notas con octava ej: Do4, Mi4, Sol4]
    F --> G[Se compara con la disposición del acorde objetivo]
    G --> H[Se muestra retroalimentación visual]
~~~

## 4. Archivos Principales y Responsabilidades
- **Controladores de Ejercicios y Auth**: Procesan las reglas de negocio y restricciones de roles.
- **Vistas Blade de Práctica**: Contienen la lógica de JavaScript embebida para el manejo de `AudioContext` y nodos de análisis FFT.
- **Migraciones y Seeders**: Configuran la persistencia de metadatos JSON y los catálogos iniciales.

## 5. Explicación Sencilla del Algoritmo FFT
La Transformada Rápida de Fourier (**FFT**) toma la señal de audio capturada en el dominio del tiempo (las ondas de presión del micrófono) y la descompone en sus frecuencias individuales (dominio de la frecuencia). Esto permite identificar con precisión qué notas musicales están sonando en un instante dado mediante el análisis de los picos de amplitud espectral.

## 6. Validaciones Back-End y Seguridad
- Validación estricta de entradas en formularios (ej. rangos de 2 a 6 notas para acordes).
- Protección contra ataques de fuerza bruta limitando los intentos de inicio de sesión.
- Enmascaramiento y ocultamiento de datos sensibles (como la cédula de identidad y credenciales) durante la serialización de modelos.
- Autorización estricta basada en roles: `admin`, `docente` y `estudiante`.

## 7. Privacidad y Limitaciones
- **Procesamiento 100% local**: Ningún archivo o flujo de audio es grabado, transmitido ni almacenado en los servidores.
- **Limitaciones físicas**: El reconocimiento puede verse afectado por ruido ambiental excesivo, instrumentos desafinados, latencia del micrófono o limitaciones de hardware del dispositivo del usuario (así como la necesidad estricta de un entorno HTTPS).

## 8. Pruebas Automatizadas Existentes
El proyecto cuenta con un conjunto de pruebas exhaustivas que garantizan la estabilidad del sistema, alcanzando un total de **66 pruebas y 218 afirmaciones aprobadas** en verde.

