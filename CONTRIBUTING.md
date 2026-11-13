# Flujo de Contribución y Convenciones - ArmoníaLab

## Reglas del Equipo
1. **Nadie hace push directo a `main`.** Todo cambio pasa obligatoriamente por un Pull Request.
2. Cada funcionalidad/tarea debe estar asociada a un Issue, una rama específica y su respectivo Pull Request.
3. Cada commit debe contener una sola intención clara.
4. No se incluye código en el archivo `.env`, claves ni credenciales.

---

## Convención de Naming de Ramas
Las ramas deben seguir el siguiente prefijo según su propósito:
- `docs/` : Para documentación y guías (ej. `docs/flujo-contribucion`).
- `feat/` : Para nuevas funcionalidades (ej. `feat/ejercicios-variedad`, `feat/audio-captura`).
- `security/` : Para mejoras de seguridad y endurecimiento.
- `fix/` : Para corrección de errores (ej. `fix/audio`).
- `test/` : Para adición o mejora de pruebas.

---

## Convención de Commits (Conventional Commits)
Los mensajes de los commits deben seguir el formato `tipo(alcance): descripción breve`:
- `feat`: Nueva característica (ej. `feat(db): agregar modalidades de ejercicios`).
- `docs`: Cambios en documentación (ej. `docs(git): documentar convencion de ramas y commits`).
- `chore`: Tareas de mantenimiento o configuración (ej. `chore(github): agregar plantilla de pull requests`).
- `fix`: Corrección de errores (ej. `fix(audio): liberar el microfono`).
- `test`: Adición o corrección de pruebas (ej. `test(audio): cubrir permisos`).