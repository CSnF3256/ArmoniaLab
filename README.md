# ArmoníaLab — Plataforma de Teoría Musical y Core Armónico MVC

![Estado del proyecto](https://img.shields.io/badge/estado-avance%202%20completado-6d3fc0?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Laravel-12-ff2d20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4?style=for-the-badge&logo=php)
![Pruebas](https://img.shields.io/badge/pruebas-27%20aprobadas-1b8a5a?style=for-the-badge)
![Despliegue](https://img.shields.io/badge/despliegue-docker%20%7C%20render-0284c7?style=for-the-badge&logo=docker)

ArmoníaLab es una plataforma web educativa orientada al aprendizaje práctico de la teoría musical, desarrollada con **PHP y Laravel 12** siguiendo estrictamente el patrón arquitectónico **Modelo-Vista-Controlador (MVC)**.

Este repositorio contiene la implementación del **Core Musical de cálculo y visualización interactiva de acordes**, la **validación en Back-End de datos sensibles**, la implementación de **dropdowns dependientes en cascada**, el módulo de **ejercicios con autocorrección inmediata** y el motor de **recomendaciones personalizadas por habilidad**.

---

## 👥 Integrantes del Proyecto

- **Camila Silva** — Facultad de Ingeniería y Ciencias Aplicadas (UDLA)
- **Joel Chico** — Facultad de Ingeniería y Ciencias Aplicadas (UDLA)

**Asignatura:** ITIZ-2201 - Ingeniería Web (202710)
**Institución:** Universidad de Las Américas (UDLA)

---

## 📑 Tabla de Contenidos

1. [Novedades de este Avance](#-novedades-de-este-avance)
2. [El Core MVC: Generador y Visualizador de Acordes](#-el-core-mvc-generador-y-visualizador-de-acordes)
3. [Validación en Back-End de Datos Sensibles](#-validación-en-back-end-de-datos-sensibles)
4. [Dropdowns Dependientes en Cascada (Cascading Selects)](#-dropdowns-dependientes-en-cascada-cascading-selects)
5. [Ejercicios con Autocorrección y Recomendaciones Adaptativas](#-ejercicios-con-autocorrección-y-recomendaciones-adaptativas)
6. [Arquitectura MVC y Componentes](#-arquitectura-mvc-y-componentes)
7. [Usuarios de Demostración](#-usuarios-de-demostración)
8. [Instalación y Ejecución Local](#-instalación-y-ejecución-local)
9. [Batería de Pruebas Automáticas](#-batería-de-pruebas-automáticas)
10. [Guía de Despliegue en un Servidor (Fuera de Localhost)](#-guía-de-despliegue-en-un-servidor-fuera-de-localhost)
11. [Control de Versiones en Git](#-control-de-versiones-en-git)

---

## 🚀 Novedades de este Avance

En cumplimiento de las directrices académicas y la retroalimentación docente recibida, este incremento incorpora:

1. **Validación en Back-End de Datos Sensibles:** Implementación de la regla personalizada `ValidEcuadorianCedula` que valida cédulas de identidad ecuatorianas mediante el algoritmo oficial de **Módulo 10 del Registro Civil**, impidiendo su manipulación en cliente antes de persistir en base de datos. Se complementa con `ValidIntervalStructure` para la integridad armónica de las fórmulas musicales.
2. **Dropdowns Dependientes / en Cascada:** Formularios donde ninguna clave foránea se ingresa mediante campos de texto manuales. Implementado en dos contextos:
   - **Geográfico:** País → Provincia → Ciudad en el registro y gestión de usuarios.
   - **Académico:** Curso → Lección dependiente en la creación administrativa de ejercicios.
3. **Core Musical Interactivo:** Motor armónico basado en aritmética modular de 12 clases cromáticas, teclado de piano interactivo SVG/CSS con iluminación de teclas y síntesis de audio polifónica en tiempo real mediante **Web Audio API**.
4. **Ejercicios con Corrección Automática:** Validación en servidor de respuestas de estudiantes, registro histórico de intentos con tiempo y puntaje.
5. **Diagnóstico y Recomendaciones Personalizadas:** Análisis algorítmico de porcentaje de acierto por habilidad que detecta debilidades y genera dinámicamente rutas de práctica personalizadas.
6. **Configuración para Despliegue en Servidor en la Nube:** Archivos `Dockerfile`, `docker/start.sh` con puerto dinámico y `render.yaml` listos para desplegar en plataformas cloud como Render, Railway o VPS.

---

## 🎹 El Core MVC: Generador y Visualizador de Acordes

El núcleo funcional del proyecto permite al estudiante seleccionar una nota fundamental y un tipo de acorde, procesar los semitonos de intervalo y visualizar el resultado tanto en notación musical como en un teclado virtual interactivo.

### 1. Entradas y Salidas Verificables

- **Entradas:**
  - Nota fundamental: Cualquiera de las 12 clases cromáticas (`Do`, `Do#`, `Re`, `Re#`, `Mi`, `Fa`, `Fa#`, `Sol`, `Sol#`, `La`, `La#`, `Si`).
  - Tipo de acorde: Modelo `TipoAcorde` (`Mayor`, `Menor`, `Séptima dominante`, `Séptima mayor`, `Disminuido`, `Aumentado`, `Sus4`, `Sus2`).
- **Proceso:** Validación en controlador, recuperación de intervalos en semitonos desde la base de datos y cálculo en `AcordeService`.
- **Fórmula Matemática:**
  $$\text{Posición Cromatic} = (\text{Índice Fundamental} + \text{Intervalo en Semitonos}) \pmod{12}$$
- **Salidas:**
  - Nombre completo y cifrado anglosajón (ej: `Do Mayor` / `C`, `La menor` / `Am`, `Sol 7` / `G7`).
  - Lista de notas integrantes y función interválica (Tónica, 3ra mayor/menor, 5ta justa, 7ma dominante).
  - Teclado virtual interactivo con teclas resaltadas en la escala cromática.
  - Síntesis polifónica con frecuencias audibles reales sintetizadas por el navegador.

### 2. Criterios de Validación Básicos Comprobados

- **Do Mayor:** Produce las notas `Do`, `Mi` y `Sol` con intervalos cromáticos `[0, 4, 7]`.
- **Do Menor:** Produce `Do`, `Re#` (Mi bemol) y `Sol` con intervalos `[0, 3, 7]`.
- **Séptima Dominante:** Produce cuatro notas (ej: Sol 7 → `Sol`, `Si`, `Re`, `Fa`) con intervalos `[0, 4, 7, 10]`.
- **Validación:** Si se envían notas o tipos inexistentes, el servidor responde con errores descriptivos sin ejecutar el cálculo.

---

## 🛡️ Validación en Back-End de Datos Sensibles

De acuerdo con el requerimiento de que *"El Admin debe tener al menos 1 validación en Back-End de algún dato sensible para su core y esta no puede ser validada solo con JS"*:

### 1. Cédula de Identidad Ecuatoriana (`ValidEcuadorianCedula`)

La cédula de identidad es un dato de carácter sensible y crítico en la identificación de estudiantes y docentes en el sistema académico. Se implementó una regla en Laravel (`app/Rules/ValidEcuadorianCedula.php`) ejecutada en el controlador antes de interactuar con la base de datos:

1. **Longitud:** Exactamente 10 dígitos numéricos (`^[0-9]{10}$`).
2. **Código de Provincia:** Los dos primeros dígitos deben pertenecer a una provincia legal de Ecuador (`01` a `24`, o `30` para consulados).
3. **Tipo de Persona:** El tercer dígito para personas naturales debe ser estrictamente menor a 6 (`0` a `5`).
4. **Algoritmo de Módulo 10:**
   - Coeficientes multiplicadores: `[2, 1, 2, 1, 2, 1, 2, 1, 2]`.
   - Para cada uno de los 9 primeros dígitos, se multiplica por su coeficiente. Si el resultado es $\ge 10$, se resta 9.
   - Se suma el total. El residuo es $\text{suma} \pmod{10}$.
   - Dígito verificador esperado: $(10 - \text{residuo}) \pmod{10}$.
   - Si no coincide exactamente con el décimo dígito, el Back-End rechaza la petición con código de validación HTTP 422.

### 2. Estructura Armónica de Intervalos (`ValidIntervalStructure`)

Dato sensible para el Core de teoría musical. Al registrar o modificar tipos de acordes en el Panel de Administración:
- Debe comenzar obligatoriamente en `0` (la tónica armónica).
- Debe tener al menos 3 notas (tríada mínima).
- Los semitonos deben ser números enteros estrictamente ascendentes (`0, 4, 7`).
- Ningún intervalo puede superar los 24 semitonos (2 octavas).

---

## 🔗 Dropdowns Dependientes en Cascada (Cascading Selects)

Cumpliendo la directiva de que las relaciones hacia otras tablas no pueden ser inputs simples de texto:

### Contexto 1: Ubicación Geográfica (País → Provincia → Ciudad)
- Utilizado en el Panel Admin (`/admin/usuarios/create` y `/admin/usuarios/{id}/edit`).
- Al seleccionar un **País** (ej: Ecuador), una llamada asíncrona a `/api/paises/{id}/provincias` carga dinámicamente el dropdown de **Provincias** pertenecientes a ese país.
- Al seleccionar una **Provincia** (ej: Pichincha), `/api/provincias/{id}/ciudades` puebla las **Ciudades** (Quito, Rumiñahui, etc.).
- En el Back-End, `AdminUserController@store` valida mediante reglas `Rule::exists` que la provincia pertenezca al país seleccionado y que la ciudad pertenezca a la provincia seleccionada, garantizando integridad referencial.

### Contexto 2: Jerarquía Académica (Curso → Lección)
- Utilizado en la creación de ejercicios (`/admin/ejercicios/create`).
- El administrador selecciona el **Curso** (ej: *Fundamentos de armonía*).
- El dropdown de **Lecciones** se llena automáticamente mediante `/api/cursos/{id}/lecciones`.
- Se vincula el ejercicio a la lección seleccionada sin necesidad de manipular claves foráneas manualmente.

---

## 🧠 Ejercicios con Autocorrección y Recomendaciones Adaptativas

Atendiendo la recomendación docente: *"Se puede desarrollar ejercicios con corrección automática, registro de intentos y progreso por habilidad. Usen los resultados para proponer prácticas y validar respuestas de tal forma que sea algo un poco mas personalizado."*

### 1. Corrección Automática en el Servidor (`EjercicioController@responder`)
- El estudiante responde una pregunta interactiva con apoyo del teclado virtual.
- La respuesta viaja al servidor donde se evalúa contra la clave armónica esperada.
- Se registra de forma persistente un registro en la tabla `intentos` (`user_id`, `ejercicio_id`, `respuesta_usuario`, `es_correcta`, `puntaje_obtenido`, `explicacion_resultado`, `tiempo_segundos`, `habilidad`).
- Se actualiza el progreso en `progreso_lecciones`.

### 2. Motor de Recomendaciones Personalizadas (`RecomendacionService`)
- El sistema agrupa los intentos del usuario por habilidad (`Tríadas Mayores`, `Tríadas Menores`, `Acordes de Séptima`, `Intervalos Armónicos`).
- Clasifica el nivel de dominio del alumno:
  - **Dominado:** Precisión $\ge 80\%$.
  - **En Progreso:** Precisión entre $60\%$ y $79\%$.
  - **Requiere Refuerzo (Habilidad Crítica):** Precisión $< 60\%$.
- Cuando detecta debilidades (por ejemplo, fallos repetidos en *Tríadas Menores*), genera un banner dinámico de **"Práctica Inteligente Sugerida"** recomendando ejercicios específicos para superar esas debilidades.

---

## 🏗️ Arquitectura MVC y Componentes

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AcordeController.php           # Core MVC: cálculo, visualización y biblioteca
│   │   ├── AuthController.php             # Login, registro y cierre de sesión
│   │   ├── CourseController.php           # CRUD de cursos de música
│   │   ├── EjercicioController.php        # Autocorrección e interactividad de ejercicios
│   │   ├── GeoController.php              # API para dropdowns dependientes en cascada
│   │   ├── ProgresoController.php         # Analítica y diagnóstico por habilidad
│   │   └── Admin/
│   │       ├── AdminDashboardController.php  # Métricas generales del sistema
│   │       ├── AdminEjercicioController.php  # Ejercicios con cascada Curso->Lección
│   │       ├── AdminTipoAcordeController.php # Core: validación de intervalos
│   │       └── AdminUserController.php       # Usuarios: validación sensible de cédula
│   └── Middleware/
│       └── EnsureUserIsAdmin.php          # Protección de rutas administrativas
├── Models/
│   ├── AcordeGuardado.php                 # Biblioteca personal del estudiante
│   ├── Ciudad.php                         # Geografía cascada
│   ├── Course.php                         # Cursos musicales
│   ├── Ejercicio.php                      # Preguntas de teoría musical
│   ├── Intento.php                        # Registro histórico de evaluaciones
│   ├── Leccion.php                        # Lecciones pedagógicas
│   ├── Pais.php                           # Geografía cascada
│   ├── ProgresoLeccion.php                # Estado y porcentaje de avance
│   ├── Provincia.php                      # Geografía cascada
│   ├── TipoAcorde.php                     # Fórmulas de acordes
│   └── User.php                           # Usuarios con roles, cédula y ubicación
├── Rules/
│   ├── ValidEcuadorianCedula.php          # Validación sensible Back-End: Módulo 10
│   └── ValidIntervalStructure.php         # Validación sensible Back-End: Intervalos
└── Services/
    ├── AcordeService.php                  # Cálculo armónico modular y frecuencias
    └── RecomendacionService.php           # Analítica y recomendaciones adaptativas
```

---

## 🔑 Usuarios de Demostración

El seeder del sistema (`php artisan db:seed`) incluye tres perfiles precargados:

| Rol | Correo Electrónico | Contraseña | Cédula Validada | Propósito |
| :--- | :--- | :--- | :--- | :--- |
| **Administrador** | `admin@armonialab.test` | `password` | `1710034065` (Pichincha) | Acceso al panel administrativo, validación de cédulas y dropdowns |
| **Docente** | `demo@armonialab.test` | `password` | `0915284392` (Guayas) | Gestión de cursos y revisión de lecciones |
| **Estudiante** | `estudiante@armonialab.test` | `password` | `0102030408` (Azuay) | Cuenta con intentos previos para ver el diagnóstico y recomendaciones |

---

## 💻 Instalación y Ejecución Local

### Requisitos
- PHP 8.2 o superior con extensiones `pdo_sqlite`, `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml` y `curl`.
- Composer 2.x

### Pasos de Instalación

1. Clonar el repositorio y situarse en la rama de entrega:
   ```bash
   git clone <URL_DEL_REPOSITORIO>
   cd ProyectoFinal
   git switch main
   ```

2. Instalar dependencias:
   ```bash
   composer install
   ```

3. Configurar el entorno:
   ```bash
   # En Linux / Mac / Git Bash:
   cp .env.example .env

   # En Windows PowerShell:
   Copy-Item .env.example .env

   php artisan key:generate
   ```

4. Crear la base de datos y cargar las migraciones con datos iniciales:
   ```bash
   # En Windows PowerShell:
   New-Item database/database.sqlite -ItemType File -Force

   # En Linux / Mac:
   touch database/database.sqlite

   php artisan migrate --seed
   ```

5. Iniciar el servidor local:
   ```bash
   php artisan serve
   ```
   Abrir en el navegador: `http://127.0.0.1:8000`

---

## 🧪 Batería de Pruebas Automáticas

El proyecto cuenta con **30 pruebas automáticas** que cubren los requerimientos y reglas de negocio implementados:

```bash
php artisan test
```

### Resumen de Suites de Pruebas:
- `AdminCoreCrudTest`: Verifica la edición y actualización de tipos de acordes y ejercicios, incluyendo el rechazo de relaciones Curso → Lección inconsistentes.
- `MusicalCoreTest`: Comprueba los criterios 1, 2, 3 y 4 del documento de análisis (Do Mayor, Do Menor, Séptima dominante, validación de parámetros y biblioteca personal).
- `BackendValidationTest`: Verifica la aceptación de cédulas ecuatorianas válidas por Módulo 10 y el rechazo estricto en el servidor de cédulas manipuladas, códigos de provincia fuera de rango y fórmulas musicales inconsistentes.
- `CascadingDropdownsTest`: Verifica que los endpoints de geografía y cursos retornen exclusivamente los hijos de la entidad seleccionada y rechace en el Back-End la asignación de claves foráneas no pertenecientes al padre.
- `EjercicioAutocorrectionTest`: Evalúa que el motor de autocorrección asigne puntajes, registre intentos en base de datos y active recomendaciones personalizadas ante fallos.
- `AuthenticationTest` y `CourseCrudTest`: Verifican la seguridad por middleware, sesión y control de acceso propietario.

---

## 🌐 Guía de Despliegue en un Servidor (Fuera de Localhost)

Para cumplir con el requerimiento de tener la aplicación **deployada en un servidor en la nube y no en localhost**, el proyecto incluye configuración lista para despliegue automatizado.

### Opción A: Render + Neon PostgreSQL (Configuración utilizada)

La aplicación está desplegada como servicio Docker en Render y utiliza una base PostgreSQL persistente alojada en Neon. SQLite se conserva únicamente como alternativa sencilla para desarrollo local.

1. Crear un proyecto PostgreSQL en [Neon](https://neon.tech) y copiar su cadena de conexión con SSL y conexión agrupada (pooled connection).
2. Crear un Web Service en [Render](https://render.com), conectar este repositorio y seleccionar:
   - **Environment:** `Docker`
   - **Branch:** `main`
   - **Plan:** Free
3. Configurar las variables de entorno en Render:

   ```ini
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://armonialab.onrender.com
   APP_KEY=base64:CLAVE_GENERADA_POR_LARAVEL
   DB_CONNECTION=pgsql
   DB_URL=postgresql://USUARIO:CONTRASENA@HOST-POOLER/BASE?sslmode=require
   SESSION_DRIVER=database
   CACHE_STORE=database
   ```

   `DB_URL` es un secreto y nunca debe guardarse en Git ni compartirse públicamente.

4. Render construye el `Dockerfile`, que instala el controlador `pdo_pgsql` requerido por Laravel.
5. Durante cada despliegue, `docker/start.sh` ejecuta de forma segura:

   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

6. La aplicación pública se encuentra en [https://armonialab.onrender.com](https://armonialab.onrender.com), fuera de localhost y con HTTPS.

Para comprobar que PostgreSQL contiene las tablas reales del sistema, se puede ejecutar en el SQL Editor de Neon:

```sql
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'public'
ORDER BY table_name;
```

### Opción B: Despliegue en Railway.app

1. Entra a [Railway.app](https://railway.app).
2. Selecciona **New Project → Deploy from GitHub repo**.
3. Selecciona el repositorio de ArmoníaLab.
4. Railway detectará el `Dockerfile` automáticamente.
5. En la pestaña **Variables**, agrega `APP_KEY` con tu clave de Laravel.
6. En **Settings → Networking**, genera un dominio público (ej: `armonialab.up.railway.app`).

### Opción C: Despliegue en VPS (Ubuntu 22.04 / 24.04 con Docker o Nginx)

Si se dispone de un servidor VPS (AWS EC2, DigitalOcean, Hetzner, etc.):

```bash
# 1. Clonar el proyecto en el servidor
git clone <URL_DEL_REPOSITORIO> /var/www/armonialab
cd /var/www/armonialab

# 2. Levantar con Docker Compose
docker compose up -d --build
```
El servidor quedará operativo en el puerto configurado o detrás de un Reverse Proxy con Nginx y Certbot para certificado SSL Let's Encrypt.

---

## 🌿 Control de Versiones en Git

El código del proyecto está versionado en Git. El avance fue desarrollado inicialmente en una rama de funcionalidad y después integrado y verificado en la rama principal:

```bash
main
```

Para obtener la versión evaluada más reciente:

```bash
git switch main
git pull origin main
git push origin main
```

---

## 📜 Licencia y Uso

Proyecto académico desarrollado para la materia de **Ingeniería Web** de la Universidad de Las Américas (UDLA). Uso educativo y demostrativo.
