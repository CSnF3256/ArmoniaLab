@extends('layouts.app')

@section('title', 'Crear Ejercicio con Dropdowns en Cascada | Admin ArmoníaLab')

@section('content')
<div class="form-container">
    <div class="breadcrumb mb-3">
        <a href="{{ route('admin.ejercicios.index') }}">← Volver a Ejercicios</a>
    </div>

    <div class="card form-card">
        <div class="form-header mb-4">
            <span class="eyebrow">Relación en Cascada: Curso → Lección</span>
            <h1>Crear Nuevo Ejercicio Interactivo</h1>
            <p class="subtitle">Este formulario demuestra la selección en cascada en el ámbito académico: al elegir un <strong>Curso</strong>, el dropdown de <strong>Lecciones</strong> se actualiza dinámicamente vía AJAX sin necesidad de ingresar manualmente el ID de clave foránea.</p>
        </div>

        <form method="POST" action="{{ route('admin.ejercicios.store') }}">
            @csrf

            <!-- DROPDOWNS DEPENDIENTES: CURSO -> LECCIÓN -->
            <div class="cascading-section mb-4">
                <div class="form-grid">
                    <div>
                        <label for="course_id">1. Seleccionar Curso *</label>
                        <select id="course_id" name="course_id" required onchange="cargarLecciones(this.value)">
                            <option value="">-- Elige un Curso --</option>
                            @foreach($cursos as $c)
                                <option value="{{ $c->id }}" @selected(old('course_id') == $c->id)>
                                    {{ $c->title }} ({{ $c->level }})
                                </option>
                            @endforeach
                        </select>
                        @error('course_id') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="leccion_id">2. Lección Correspondiente (Dropdown Dependiente) *</label>
                        <select id="leccion_id" name="leccion_id" required disabled>
                            <option value="">-- Primero elija un curso --</option>
                        </select>
                        <p class="field-help text-sm text-muted">Cargado dinámicamente según el curso seleccionado.</p>
                        @error('leccion_id') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="form-grid mb-3">
                <div>
                    <label for="tipo_acorde_id">Tipo de Acorde Asociado (Opcional)</label>
                    <select id="tipo_acorde_id" name="tipo_acorde_id">
                        <option value="">-- Ninguno / Ejercicio de Intervalos General --</option>
                        @foreach($tiposAcordes as $t)
                            <option value="{{ $t->id }}" @selected(old('tipo_acorde_id') == $t->id)>
                                {{ $t->nombre }} ({{ $t->simbolo ?: 'M' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="habilidad">Habilidad Musical Evaluada *</label>
                    <input id="habilidad" name="habilidad" type="text" value="{{ old('habilidad') }}" placeholder="Ej: Tríadas Mayores, Intervalos, etc." required>
                    @error('habilidad') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-grid mb-3">
                <div>
                    <label for="dificultad">Dificultad *</label>
                    <select id="dificultad" name="dificultad" required>
                        <option value="Fácil" @selected(old('dificultad') === 'Fácil')>Fácil</option>
                        <option value="Medio" @selected(old('dificultad') === 'Medio')>Medio</option>
                        <option value="Avanzado" @selected(old('dificultad') === 'Avanzado')>Avanzado</option>
                    </select>
                </div>

                <div>
                    <label for="tipo">Tipo de Ejercicio *</label>
                    <select id="tipo" name="tipo" required>
                        <option value="opcion_multiple">Opción Múltiple</option>
                        <option value="identificar_notas">Identificar Notas</option>
                        <option value="construir_acorde">Construcción en Teclado</option>
                        <option value="intervalo">Cálculo de Intervalo</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="pregunta">Pregunta o Enunciado del Ejercicio *</label>
                <textarea id="pregunta" name="pregunta" rows="3" placeholder="Ej: ¿Cuáles son las notas integrantes del acorde de Sol Mayor (G)?" required>{{ old('pregunta') }}</textarea>
                @error('pregunta') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="options-creation-box p-3 mb-3">
                <h3>Opciones de Respuesta (Para evaluación automática)</h3>
                <div class="form-grid mt-2">
                    <div>
                        <label for="opcion_1">Opción A *</label>
                        <input id="opcion_1" name="opcion_1" type="text" value="{{ old('opcion_1') }}" placeholder="Ej: Sol - Si - Re" required>
                    </div>
                    <div>
                        <label for="opcion_2">Opción B *</label>
                        <input id="opcion_2" name="opcion_2" type="text" value="{{ old('opcion_2') }}" placeholder="Ej: Sol - Do - Re" required>
                    </div>
                </div>
                <div class="form-grid mt-2">
                    <div>
                        <label for="opcion_3">Opción C *</label>
                        <input id="opcion_3" name="opcion_3" type="text" value="{{ old('opcion_3') }}" placeholder="Ej: Sol - Sib - Re" required>
                    </div>
                    <div>
                        <label for="opcion_4">Opción D *</label>
                        <input id="opcion_4" name="opcion_4" type="text" value="{{ old('opcion_4') }}" placeholder="Ej: La - Do# - Mi" required>
                    </div>
                </div>
            </div>

            <div class="form-grid mb-3">
                <div>
                    <label for="respuesta_correcta">Respuesta Correcta Exacta *</label>
                    <input id="respuesta_correcta" name="respuesta_correcta" type="text" value="{{ old('respuesta_correcta') }}" placeholder="Debe coincidir con una de las opciones (Ej: Sol - Si - Re)" required>
                    <p class="field-help text-sm text-muted">El motor en el Back-End comparará esta clave para asignar acierto o fallo.</p>
                    @error('respuesta_correcta') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="puntos">Puntos Asignados *</label>
                    <input id="puntos" name="puntos" type="number" min="1" max="100" value="{{ old('puntos', 10) }}" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="explicacion">Explicación Teórica / Retroalimentación para el Alumno</label>
                <textarea id="explicacion" name="explicacion" rows="2" placeholder="Ej: Sol Mayor aplica la fórmula [0, 4, 7]: Sol (tónica), Si (3ra mayor a 4 st) y Re (5ta justa a 7 st).">{{ old('explicacion') }}</textarea>
            </div>

            <div class="actions">
                <button type="submit" class="button large">💾 Guardar Ejercicio con Cascada</button>
                <a href="{{ route('admin.ejercicios.index') }}" class="button secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const oldLeccionId = "{{ old('leccion_id') }}";

async function cargarLecciones(courseId, preseleccionarId = null) {
    const selectLeccion = document.getElementById('leccion_id');

    selectLeccion.innerHTML = '<option value="">Cargando lecciones del curso...</option>';
    selectLeccion.disabled = true;

    if (!courseId) {
        selectLeccion.innerHTML = '<option value="">-- Primero elija un curso --</option>';
        return;
    }

    try {
        const res = await fetch(`/api/cursos/${courseId}/lecciones`);
        const lecciones = await res.json();

        if (lecciones.length === 0) {
            selectLeccion.innerHTML = '<option value="">(Este curso no tiene lecciones creadas todavía)</option>';
            return;
        }

        selectLeccion.innerHTML = '<option value="">-- Seleccionar Lección --</option>';
        lecciones.forEach(lec => {
            const opt = document.createElement('option');
            opt.value = lec.id;
            opt.textContent = `${lec.titulo} [Habilidad: ${lec.habilidad}]`;
            if (preseleccionarId && String(preseleccionarId) === String(lec.id)) {
                opt.selected = true;
            }
            selectLeccion.appendChild(opt);
        });

        selectLeccion.disabled = false;
    } catch (e) {
        selectLeccion.innerHTML = '<option value="">Error al cargar lecciones</option>';
        console.error(e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const cursoInicial = document.getElementById('course_id').value;
    if (cursoInicial) {
        cargarLecciones(cursoInicial, oldLeccionId);
    }
});
</script>
@endpush
