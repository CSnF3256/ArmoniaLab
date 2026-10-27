# Notas para la exposición de ArmoníaLab

## Objetivo de esta entrega

Demostrar una aplicación Laravel con patrón MVC, autenticación y un CRUD al que no se puede acceder sin iniciar sesión.

## Guion sugerido de 7 minutos

1. **Problema y solución (45 s):** ArmoníaLab será una plataforma para aprender teoría musical. En esta entrega construimos la base segura para administrar cursos.
2. **Arquitectura MVC (1 min):** mostrar `Course`, `CourseController` y las vistas de `resources/views/courses`.
3. **Prueba de seguridad (1 min):** cerrar sesión, escribir `/cursos` y enseñar la redirección automática al login.
4. **Login (45 s):** entrar con el usuario demo y explicar que Laravel guarda la sesión.
5. **CRUD (2 min):** crear un curso, verlo, editarlo y eliminarlo.
6. **Validaciones y autorización (45 s):** intentar guardar un formulario vacío; explicar que un usuario no puede editar cursos ajenos.
7. **Código y cierre (50 s):** mostrar las rutas dentro de `Route::middleware('auth')`, mencionar las pruebas y explicar el siguiente avance.

## Frases clave para explicar MVC

- El **modelo** representa los datos y relaciones de un curso.
- La **vista** contiene el HTML que ve el usuario.
- El **controlador** recibe la petición y coordina el proceso.
- El middleware `auth` se ejecuta antes del controlador y bloquea a los visitantes.
- `CourseRequest` valida los datos para que el controlador sea corto y claro.

## Demostración paso a paso

- [ ] Tener el servidor iniciado con `php artisan serve`.
- [ ] Tener lista la cuenta `demo@armonialab.test` / `password`.
- [ ] Abrir una ventana privada para demostrar `/cursos` sin sesión.
- [ ] Iniciar sesión y crear un curso.
- [ ] Mostrar la lista, el detalle y la edición.
- [ ] Eliminar el curso.
- [ ] Mostrar `routes/web.php` y las pruebas.

## Preguntas que podrían hacer

**¿Por qué las rutas están protegidas?**  
Porque el recurso `cursos` está dentro de un grupo con middleware `auth`. Sin una sesión válida, Laravel redirige al login.

**¿Dónde se aplica MVC?**  
`Course` es el modelo, `CourseController` es el controlador y las plantillas Blade de `courses` son las vistas.

**¿Cómo evitan que un usuario edite datos ajenos?**  
Se compara el `user_id` del curso con el usuario autenticado. Si no coincide, se responde con error 403.

**¿Qué significa CRUD?**  
Create, Read, Update y Delete: crear, leer, actualizar y eliminar.

**¿Qué falta del proyecto completo?**  
Roles, lecciones, ejercicios, progreso y el motor armónico. Se agregarán en avances posteriores.

## Plan B para la exposición

Si falla Internet no importa: la aplicación funciona localmente. Antes de la clase, verificar PHP, Composer, la base de datos y el archivo `.env`.

## Espacio de notas del equipo

### Integrantes y reparto

- Integrante A:
- Integrante B:
- Quién presenta cada sección:

### Cambios solicitados por el docente

-
-
-

### Errores encontrados y solución

- Fecha:
- Error:
- Solución:

### Pendientes para la próxima sesión

- [ ]
- [ ]
- [ ]

### Preguntas para el docente

-
-
-
