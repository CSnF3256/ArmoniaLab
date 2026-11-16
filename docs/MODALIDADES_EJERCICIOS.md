# Modalidades de Ejercicios Disponibles

> **Rama:** `feat/ejercicios-variedad`  
> **PR:** `feat(ejercicios): ampliar modalidades de practica musical`  
> **Revisora:** Camila

---

## ¿Qué es una Modalidad?

Una **modalidad** define el formato visual e interactivo con el que se presenta un ejercicio al estudiante. Es independiente del campo `tipo` (que categoriza el contenido armónico evaluado). La columna `modalidad` se agregó a la tabla `ejercicios` mediante la migración `2026_11_14_000001_add_modalidad_to_ejercicios_table`.

La migración también agrega una columna `metadata` (JSON nullable) para almacenar parámetros adicionales como pistas contextuales y tiempo límite de respuesta.

---

## Catálogo de Modalidades

| Clave | Etiqueta | Descripción |
|---|---|---|
| `clasica` | Clásica (opción múltiple estándar) | Presenta 4 opciones de respuesta con letras A-B-C-D. Es la modalidad por defecto. |
| `completar_secuencia` | Completar secuencia de notas | El estudiante elige la opción que completa una secuencia musical incompleta. Las opciones se muestran con un ícono 🎵. |
| `seleccionar_intervalo` | Seleccionar intervalo correcto | Presenta las opciones como números de semitonos en un grid 4-columnas. Ideal para ejercicios numéricos de distancias interválicas. |
| `verdadero_falso` | Verdadero o Falso | Presenta solo dos opciones (✓ Verdadero / ✗ Falso) en un grid de 2 columnas. Ideal para afirmaciones teóricas. |
| `ordenar_notas` | Ordenar notas de menor a mayor | Muestra las notas disponibles como badges y pide al estudiante escribir el orden correcto en un campo de texto libre. La autocorrección compara insensible a mayúsculas. |
| `detectar_error` | Detectar la nota errónea en el acorde | Presenta las opciones con borde rojo para enfatizar que se busca la nota incorrecta. Ideal para ejercicios de análisis armónico. |

---

## Metadatos Opcionales (`metadata`)

Cada ejercicio puede tener metadatos opcionales que se configuran desde el panel de administración:

| Clave | Tipo | Descripción |
|---|---|---|
| `pista` | `string` (máx. 255 caracteres) | Texto de ayuda que se muestra sobre el formulario de respuesta con un ícono 💡. |
| `tiempo_limite` | `integer` (0–600 segundos) | Si es mayor a 0, se muestra un contador regresivo que envía el formulario automáticamente al llegar a 0. |

---

## Dónde se Define la Lógica

### Modelo
- [`app/Models/Ejercicio.php`](../app/Models/Ejercicio.php): método estático `modalidades()` que devuelve el array `clave => etiqueta` canónico. Es la fuente de verdad para validaciones y vistas.

### Migración
- [`database/migrations/2026_11_14_000001_add_modalidad_to_ejercicios_table.php`](../database/migrations/2026_11_14_000001_add_modalidad_to_ejercicios_table.php)

### Controlador Admin
- [`app/Http/Controllers/Admin/AdminEjercicioController.php`](../app/Http/Controllers/Admin/AdminEjercicioController.php): `store()` y `update()` validan `modalidad` con la regla `in:clasica,completar_secuencia,...`.

### Vistas Admin
- [`resources/views/admin/ejercicios/create.blade.php`](../resources/views/admin/ejercicios/create.blade.php): selector de modalidad + campos de metadata.
- [`resources/views/admin/ejercicios/edit.blade.php`](../resources/views/admin/ejercicios/edit.blade.php): edición de modalidad + metadata con valores precargados.
- [`resources/views/admin/ejercicios/index.blade.php`](../resources/views/admin/ejercicios/index.blade.php): columna **Modalidad** en la tabla de gestión.

### Vista Estudiante
- [`resources/views/ejercicios/show.blade.php`](../resources/views/ejercicios/show.blade.php): bloque `@if / @elseif` que renderiza el formulario diferenciado según `$ejercicio->modalidad`. Incluye badge de modalidad en la cabecera, pista contextual y contador regresivo.

### Seeder
- [`database/seeders/DatabaseSeeder.php`](../database/seeders/DatabaseSeeder.php): banco ampliado con 10+ ejercicios nuevos cubriendo todas las modalidades y habilidades: **Acordes Alterados**, **Acordes Suspendidos**, **Séptimas avanzadas** e **Intervalos**.

### Tests
- [`tests/Feature/EjercicioModalidadesTest.php`](../tests/Feature/EjercicioModalidadesTest.php): 10 pruebas de Feature que cubren:
  - Catálogo de 6 modalidades (test_modelo_expone_seis_modalidades)
  - Valor por defecto `clasica`
  - Autocorrección para cada modalidad: verdadero_falso, ordenar_notas, seleccionar_intervalo, completar_secuencia, detectar_error
  - Persistencia de metadata
  - Validación backend de modalidad inválida
  - Badge de modalidad en la vista show

---

## Flujo de creación de ejercicio con modalidad

```
Admin elige Curso → (dropdown dependiente carga Lecciones vía AJAX)
     ↓
Admin elige Modalidad (selector con 6 opciones)
     ↓
Admin rellena Pista y Tiempo límite (opcionales)
     ↓
POST admin.ejercicios.store → AdminEjercicioController@store
     ↓
Validación backend: modalidad in:[clasica, completar_secuencia, ...]
     ↓
Ejercicio::create([..., 'modalidad' => ..., 'metadata' => [...]])
     ↓
Estudiante ve show.blade.php → @if($modalidad === 'verdadero_falso') ...
     ↓
POST ejercicios.responder → EjercicioController@responder
     ↓ (lógica de autocorrección sin cambios)
Intento guardado + Progreso actualizado
```

---

## Habilidades cubiertas en el seeder ampliado

| Habilidad | Lección | Modalidades usadas |
|---|---|---|
| Tríadas Mayores | Construcción de Tríadas Mayores | `clasica` |
| Tríadas Menores | Tríadas Menores y Sonoridad Introspectiva | `clasica` |
| Acordes de Séptima | Acordes de Séptima de Dominante | `clasica` |
| Acordes de Séptima | Séptima Mayor y Séptima Menor | `seleccionar_intervalo`, `ordenar_notas`, `verdadero_falso` |
| Intervalos Armónicos | Cálculo de Intervalos Armónicos | `clasica`, `seleccionar_intervalo`, `detectar_error` |
| **Acordes Alterados** *(nuevo)* | Acordes Disminuidos y Aumentados | `clasica`, `detectar_error` |
| **Acordes Suspendidos** *(nuevo)* | Acordes Suspendidos (sus2 y sus4) | `completar_secuencia`, `verdadero_falso` |

