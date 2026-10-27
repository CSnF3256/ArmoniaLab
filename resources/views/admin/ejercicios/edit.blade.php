@extends('layouts.app')

@section('title', 'Editar Ejercicio | Admin ArmoníaLab')

@section('content')
@php
    $opciones = array_pad($ejercicio->datos_pregunta['opciones'] ?? [], 4, '');
    $cursoSeleccionado = old('course_id', $ejercicio->leccion?->course_id);
    $leccionSeleccionada = old('leccion_id', $ejercicio->leccion_id);
@endphp

<div class="form-container">
    <div class="breadcrumb mb-3">
        <a href="{{ route('admin.ejercicios.index') }}">← Volver a Ejercicios</a>
    </div>

    <div class="card form-card">
        <div class="form-header mb-4">
            <span class="eyebrow">CRUD Administrativo — Actualizar</span>
            <h1>Editar Ejercicio Interactivo</h1>
            <p class="subtitle">La relación Curso → Lección sigue siendo un dropdown dependiente y el servidor verifica que ambas selecciones sean consistentes.</p>
        </div>

        <form method="POST" action="{{ route('admin.ejercicios.update', $ejercicio) }}">
            @csrf
            @method('PUT')

            <div class="cascading-section mb-4">
                <div class="form-grid">
                    <div>
                        <label for="course_id">1. Seleccionar Curso *</label>
                        <select id="course_id" name="course_id" required onchange="cargarLecciones(this.value)">
                            <option value="">-- Elige un Curso --</option>
                            @foreach($cursos as $curso)
                                <option value="{{ $curso->id }}" @selected($cursoSeleccionado == $curso->id)>{{ $curso->title }} ({{ $curso->level }})</option>
                            @endforeach
                        </select>
                        @error('course_id') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="leccion_id">2. Lección Correspondiente *</label>
                        <select id="leccion_id" name="leccion_id" required disabled>
                            <option value="">Cargando lecciones...</option>
                        </select>
                        @error('leccion_id') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="form-grid mb-3">
                <div>
                    <label for="tipo_acorde_id">Tipo de Acorde Asociado</label>
                    <select id="tipo_acorde_id" name="tipo_acorde_id">
                        <option value="">-- Ninguno --</option>
                        @foreach($tiposAcordes as $tipoAcorde)
                            <option value="{{ $tipoAcorde->id }}" @selected(old('tipo_acorde_id', $ejercicio->tipo_acorde_id) == $tipoAcorde->id)>{{ $tipoAcorde->nombre }} ({{ $tipoAcorde->simbolo }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="habilidad">Habilidad Musical Evaluada *</label>
                    <input id="habilidad" name="habilidad" type="text" value="{{ old('habilidad', $ejercicio->habilidad) }}" required>
                    @error('habilidad') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-grid mb-3">
                <div>
                    <label for="dificultad">Dificultad *</label>
                    <select id="dificultad" name="dificultad" required>
                        @foreach(['Fácil', 'Medio', 'Avanzado'] as $dificultad)
                            <option value="{{ $dificultad }}" @selected(old('dificultad', $ejercicio->dificultad) === $dificultad)>{{ $dificultad }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tipo">Tipo de Ejercicio *</label>
                    <select id="tipo" name="tipo" required>
                        @foreach(['opcion_multiple' => 'Opción Múltiple', 'identificar_notas' => 'Identificar Notas', 'construir_acorde' => 'Construcción en Teclado', 'intervalo' => 'Cálculo de Intervalo'] as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(old('tipo', $ejercicio->tipo) === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="pregunta">Pregunta o Enunciado *</label>
                <textarea id="pregunta" name="pregunta" rows="3" required>{{ old('pregunta', $ejercicio->pregunta) }}</textarea>
                @error('pregunta') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="options-creation-box p-3 mb-3">
                <h3>Opciones de Respuesta</h3>
                @for($i = 0; $i < 4; $i++)
                    <div class="mt-2">
                        <label for="opcion_{{ $i + 1 }}">Opción {{ chr(65 + $i) }} *</label>
                        <input id="opcion_{{ $i + 1 }}" name="opcion_{{ $i + 1 }}" type="text" value="{{ old('opcion_'.($i + 1), $opciones[$i]) }}" required>
                        @error('opcion_'.($i + 1)) <p class="error">{{ $message }}</p> @enderror
                    </div>
                @endfor
            </div>

            <div class="form-grid mb-3">
                <div>
                    <label for="respuesta_correcta">Respuesta Correcta Exacta *</label>
                    <input id="respuesta_correcta" name="respuesta_correcta" type="text" value="{{ old('respuesta_correcta', $ejercicio->respuesta_correcta) }}" required>
                    @error('respuesta_correcta') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="puntos">Puntos *</label>
                    <input id="puntos" name="puntos" type="number" min="1" max="100" value="{{ old('puntos', $ejercicio->puntos) }}" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="explicacion">Explicación / Retroalimentación</label>
                <textarea id="explicacion" name="explicacion" rows="3">{{ old('explicacion', $ejercicio->explicacion) }}</textarea>
            </div>

            <div class="actions">
                <button type="submit" class="button large">💾 Actualizar Ejercicio</button>
                <a href="{{ route('admin.ejercicios.index') }}" class="button secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const leccionSeleccionada = @json((string) $leccionSeleccionada);

async function cargarLecciones(courseId, preseleccionarId = null) {
    const select = document.getElementById('leccion_id');
    select.disabled = true;
    select.innerHTML = '<option value="">Cargando lecciones...</option>';

    if (!courseId) {
        select.innerHTML = '<option value="">-- Primero elija un curso --</option>';
        return;
    }

    try {
        const response = await fetch(`/api/cursos/${courseId}/lecciones`);
        if (!response.ok) throw new Error('No fue posible cargar las lecciones');
        const lecciones = await response.json();
        select.innerHTML = '<option value="">-- Seleccionar Lección --</option>';

        lecciones.forEach(leccion => {
            const option = document.createElement('option');
            option.value = leccion.id;
            option.textContent = `${leccion.titulo} [${leccion.habilidad}]`;
            option.selected = String(leccion.id) === String(preseleccionarId);
            select.appendChild(option);
        });
        select.disabled = false;
    } catch (error) {
        select.innerHTML = '<option value="">Error al cargar lecciones</option>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    cargarLecciones(document.getElementById('course_id').value, leccionSeleccionada);
});
</script>
@endpush
