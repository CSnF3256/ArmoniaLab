# ArmoníaLab — Plataforma de Teoría Musical y Core Armónico MVC

![Estado del proyecto](https://img.shields.io/badge/estado-avance%20audio%20y%20seguridad-6d3fc0?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Laravel-12-ff2d20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4?style=for-the-badge&logo=php)
![Pruebas](https://img.shields.io/badge/pruebas-66%20aprobadas-1b8a5a?style=for-the-badge)
![Despliegue](https://img.shields.io/badge/despliegue-docker%20%7C%20render-0284c7?style=for-the-badge&logo=docker)

ArmoníaLab es una plataforma web educativa orientada al aprendizaje práctico de la teoría musical, desarrollada con **PHP y Laravel 12** siguiendo estrictamente el patrón arquitectónico **Modelo-Vista-Controlador (MVC)**.

Este repositorio contiene la implementación del **Core Musical de cálculo y visualización interactiva de acordes**, la **validación en Back-End de datos sensibles**, la implementación de **dropdowns dependientes en cascada**, el módulo de **ejercicios con autocorrección inmediata**, el motor de **reconocimiento de audio en tiempo real mediante Web Audio API (FFT y RMS)** y las robustas **capas de seguridad, autenticación y despliegue en producción con PostgreSQL Neon y Render**.

---

## 👥 Integrantes del Proyecto

- **Camila Silva** — Facultad de Ingeniería y Ciencias Aplicadas (UDLA)
- **Joel Chico** — Facultad de Ingeniería y Ciencias Aplicadas (UDLA)

**Asignatura:** ITIZ-2201 - Ingeniería Web (202710)
**Institución:** Universidad de Las Américas (UDLA)

---

## 📑 Tabla de Contenidos

1. Novedades de este Avance
2. Avance: Audio y Seguridad (Reconocimiento en Tiempo Real)
3. El Core MVC: Generador y Visualizador de Acordes
4. Validación en Back-End de Datos Sensibles
5. Dropdowns Dependientes en Cascada (Cascading Selects)
6. Ejercicios con Autocorrección y Recomendaciones Adaptativas
7. Arquitectura MVC y Componentes
8. Usuarios de Demostración
9. Instalación y Ejecución Local
10. Batería de Pruebas Automáticas
11. Guía de Despliegue en un Servidor (Fuera de Localhost)
12. Control de Versiones en Git

---

## 🚀 Novedades de este Avance

En cumplimiento de las directrices académicas y la integración de las últimas funcionalidades, este incremento incorpora:

1. **Reconocimiento de Audio en Tiempo Real:** Integración de la Web Audio API para captura de micrófono mediante `getUserMedia`, análisis de volumen con **RMS**, descomposición espectral mediante **Transformada Rápida de Fourier (FFT)** y reconocimiento de notas con formato de octava estricto (ej: `Do4`, `Mi4`, `Sol4`).
2. **Validación en Back-End de Datos Sensibles:** Implementación de la regla personalizada `ValidEcuadorianCedula` (algoritmo Módulo 10 del Registro Civil) y `ValidIntervalStructure` para la integridad armónica, sumado a la validación estricta en servidor de arreglos musicales de 2 a 6 notas.
3. **Dropdowns Dependientes / en Cascada:** Formularios geográficos (País -> Provincia -> Ciudad) y académicos (Curso -> Lección) sin entradas de texto manuales para claves foráneas.
4. **Seguridad y Privacidad Avanzada:** Autenticación protegida con control de tasa (rate limiting) contra fuerza bruta, autorización estricta por roles (*admin*, *docente*, *estudiante*), enmascaramiento de datos sensibles en serialización y procesamiento de audio 100% local (sin almacenamiento ni transmisión de grabaciones de voz).
5. **Despliegue en la Nube:** Operatividad en producción mediante contenedores Docker sobre **PostgreSQL Neon** y **Render** bajo protocolo HTTPS (requisito indispensable para APIs de captura de micrófono).

---

## 🎙️ Avance: Audio y Seguridad (Reconocimiento en Tiempo Real)

El módulo de audio y seguridad implementa un flujo interactivo avanzado para la práctica instrumental y vocal:

- **Captura de Micrófono y RMS:** El navegador solicita permisos mediante `getUserMedia` y calcula de forma instantánea el nivel de presión sonora (volumen) utilizando la raíz cuadrática media (**RMS**).
- **Análisis Espectral con FFT:** La señal de audio es procesada mediante la Transformada Rápida de Fourier (**FFT**) para identificar las frecuencias dominantes, mapeándolas al sistema de notación con octava (ej: `Do4`, `Mi4`, `Sol4`).
- **Comparación Armónica:** El sistema evalúa las notas detectadas frente a una disposición concreta del acorde objetivo configurada por el docente.
- **Procesamiento Local y Privacidad:** Todo el análisis de frecuencias y volúmenes ocurre de manera local en la memoria del navegador del cliente. **El audio nunca se graba, no se transmite ni se almacena en el servidor.** Además, el micrófono se libera automáticamente al detener la sesión o abandonar la página.
- **Seguridad en Autenticación y Back-End:**
  - Control riguroso de intentos de inicio de sesión para mitigar ataques de fuerza bruta.
  - Autorización basada en roles (*admin*, *docente*, *estudiante*) validada en middleware antes de procesar controladores.
  - Validación Back-End que exige un rango de 2 a 6 notas en las estructuras armónicas.
  - Ocultamiento automático de cédulas y credenciales durante la serialización JSON de modelos.

---

## 🎹 El Core MVC: Generador y Visualizador de Acordes

El núcleo funcional del proyecto permite al estudiante seleccionar una nota fundamental y un tipo de acorde, procesar los semitonos de intervalo y visualizar el resultado tanto en notación musical como en un teclado virtual interactivo.

### Entradas y Salidas Verificables

- **Entradas:**
  - Nota fundamental: Cualquiera de las 12 clases cromáticas (`Do`, `Do#`, `Re`, `Re#`, `Mi`, `Fa`, `Fa#`, `Sol`, `Sol#`, `La`, `La#`, `Si`).
  - Tipo de acorde: Modelo `TipoAcorde` (`Mayor`, `Menor`, `Séptima dominante`, `Séptima mayor`, `Disminuido`, `Aumentado`, `Sus4`, `Sus2`).
- **Proceso:** Validación en controlador, recuperación de intervalos en semitonos desde la base de datos y cálculo en `AcordeService`.
- **Salidas:**
  - Nombre completo y cifrado anglosajón (ej: `Do Mayor` / `C`, `La menor` / `Am`, `Sol 7` / `G7`).
  - Lista de notas integrantes y función interválica.
  - Teclado virtual interactivo con teclas resaltadas en la escala cromática.
  - Síntesis polifónica con frecuencias audibles reales sintetizadas por el navegador.

---

## 🛡️ Validación en Back-End de Datos Sensibles

1. **Cédula de Identidad Ecuatoriana (`ValidEcuadorianCedula`):** Validación estricta de 10 dígitos basada en el algoritmo oficial de **Módulo 10 del Registro Civil** y rangos de provincias válidos (`01` a `24`, `30`), impidiendo manipulación en el cliente.
2. **Estructura Armónica de Intervalos (`ValidIntervalStructure`):** Verifica que las fórmulas de acordes comiencen en `0`, posean al menos 3 notas, sean estrictamente ascendentes y no superen los 24 semitonos. Adicionalmente, el Back-End valida que los ejercicios con reconocimiento de audio procesen de 2 a 6 notas válidas.

---

## 🔗 Dropdowns Dependientes en Cascada (Cascading Selects)

- **Geografía:** País -> Provincia -> Ciudad con reglas `Rule::exists` en el servidor para garantizar integridad referencial.
- **Académico:** Curso -> Lección en la creación administrativa de ejercicios, asociando el contenido sin entradas de texto manuales.

---

## 🧠 Ejercicios con Autocorrección y Recomendaciones Adaptativas

- **Autocorrección en Servidor (`EjercicioController@responder`):** Evalúa las respuestas del estudiante (incluyendo detección de notas por micrófono o selección múltiple), registrando intentos con puntaje y tiempo.
- **Recomendaciones Adaptativas (`RecomendacionService`):** Agrupa intentos por habilidad, clasifica el dominio del alumno y genera alertas dinámicas de refuerzo ante debilidades detectadas.

---

## 🏗️ Arquitectura MVC y Componentes

- `app/Http/Controllers/`: Controladores de negocio, autenticación, geo, ejercicios y panel Admin (`AcordeController`, `AuthController`, `CourseController`, `EjercicioController`, `GeoController`, `ProgresoController`, y subdirectorio `Admin/`).
- `app/Http/Middleware/`: Filtros de control de acceso por roles (`EnsureUserIsAdmin`).
- `app/Models/`: Modelos Eloquent (`User`, `Ejercicio`, `TipoAcorde`, `Course`, `Intento`, `Leccion`, `ProgresoLeccion`, `AcordeGuardado`, `Pais`, `Provincia`, `Ciudad`).
- `app/Rules/`: Reglas de validación Back-End (`ValidEcuadorianCedula`, `ValidIntervalStructure`).
- `app/Services/`: Servicios de cálculo armónico y recomendaciones (`AcordeService`, `RecomendacionService`).

---

## 🔑 Usuarios de Demostración

| Rol | Correo Electrónico | Contraseña | Cédula Validada | Propósito |
| :--- | :--- | :--- | :--- | :--- |
| **Administrador** | `admin@armonialab.test` | `password` | `1710034065` (Pichincha) | Acceso al panel administrativo y validaciones |
| **Docente** | `demo@armonialab.test` | `password` | `0915284392` (Guayas) | Gestión de cursos y configuración de notas objetivo |
| **Estudiante** | `estudiante@armonialab.test` | `password` | `0102030408` (Azuay) | Práctica de ejercicios y reconocimiento por micrófono |

---

## 💻 Instalación y Ejecución Local

### Requisitos
- PHP 8.2+ con extensiones requeridas (`pdo_sqlite`, `pdo_pgsql`, `mbstring`, `openssl`, etc.).
- Composer 2.x

### Pasos
1. Clonar el repositorio: `git clone <URL_DEL_REPOSITORIO>`
2. Ingresar al directorio: `cd ProyectoFinal`
3. Instalar dependencias: `composer install`
4. Configurar el entorno: `cp .env.example .env` y generar clave con `php artisan key:generate`
5. Crear base de datos SQLite: `touch database/database.sqlite`
6. Migrar y poblar datos: `php artisan migrate --seed`
7. Levantar servidor local: `php artisan serve` (abrir en `http://127.0.0.1:8000`)

---

## 🧪 Batería de Pruebas Automáticas

El proyecto cuenta con una robusta suite de pruebas automatizadas que cubren el funcionamiento del sistema:
- **66 pruebas y 218 afirmaciones aprobadas** en total.
- Comando de ejecución: `php artisan test`

---

## 🌐 Guía de Despliegue en Servidor en la Nube (Render + Neon)

La aplicación está desplegada en producción mediante contenedores Docker conectados a una base de datos PostgreSQL alojada en **Neon**:
- **URL Pública:** https://armonialab.onrender.com (requiere conexión **HTTPS** activa para habilitar la captura de micrófono mediante `getUserMedia`).
- Para comprobar las tablas creadas en Neon, ejecuta en su SQL Editor:
  `SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name;`

---

## 📜 Licencia y Uso

Proyecto académico desarrollado para la materia de **Ingeniería Web** de la Universidad de Las Américas (UDLA). Uso educativo y demostrativo.