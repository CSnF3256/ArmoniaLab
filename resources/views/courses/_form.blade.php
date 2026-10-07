@csrf
@if(isset($course)) @method('PUT') @endif
<label for="title">Título</label>
<input id="title" name="title" value="{{ old('title', $course->title ?? '') }}" required>
@error('title') <p class="error">{{ $message }}</p> @enderror

<label for="description">Descripción</label>
<textarea id="description" name="description" rows="6" required>{{ old('description', $course->description ?? '') }}</textarea>
@error('description') <p class="error">{{ $message }}</p> @enderror

<div class="form-grid">
    <div><label for="level">Nivel</label><select id="level" name="level" required>
        @foreach(['Principiante', 'Intermedio', 'Avanzado'] as $value)
            <option value="{{ $value }}" @selected(old('level', $course->level ?? '') === $value)>{{ $value }}</option>
        @endforeach
    </select></div>
    <div><label for="status">Estado</label><select id="status" name="status" required>
        @foreach(['Borrador', 'Publicado'] as $value)
            <option value="{{ $value }}" @selected(old('status', $course->status ?? '') === $value)>{{ $value }}</option>
        @endforeach
    </select></div>
</div>
<div class="actions"><button class="button" type="submit">{{ $buttonText }}</button><a class="button secondary" href="{{ route('cursos.index') }}">Cancelar</a></div>
