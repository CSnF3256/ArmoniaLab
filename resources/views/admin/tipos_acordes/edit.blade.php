@extends('layouts.app')

@section('title', 'Editar Tipo de Acorde | Admin ArmoníaLab')

@section('content')
<div class="form-container">
    <div class="breadcrumb mb-3">
        <a href="{{ route('admin.tipos-acordes.index') }}">← Volver a Tipos de Acordes</a>
    </div>

    <div class="card form-card">
        <div class="form-header mb-4">
            <span class="eyebrow">CRUD del Core Musical — Actualizar</span>
            <h1>Editar Tipo de Acorde</h1>
            <p class="subtitle">Los cambios alimentan directamente el constructor de acordes. La fórmula vuelve a validarse en el Back-End antes de actualizar PostgreSQL.</p>
        </div>

        <form method="POST" action="{{ route('admin.tipos-acordes.update', $tipoAcorde) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div>
                    <label for="nombre">Nombre del Acorde *</label>
                    <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $tipoAcorde->nombre) }}" required>
                    @error('nombre') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="simbolo">Símbolo o Cifrado Armónico *</label>
                    <input id="simbolo" name="simbolo" type="text" value="{{ old('simbolo', $tipoAcorde->simbolo) }}" required>
                    @error('simbolo') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-3">
                <label for="categoria">Categoría Armónica *</label>
                <select id="categoria" name="categoria" required>
                    @foreach(['Tríadas', 'Séptimas', 'Suspendidos', 'Alterados', 'Extendidos'] as $categoria)
                        <option value="{{ $categoria }}" @selected(old('categoria', $tipoAcorde->categoria) === $categoria)>{{ $categoria }}</option>
                    @endforeach
                </select>
                @error('categoria') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="sensitive-box mt-4">
                <div class="sensitive-label-group">
                    <label for="intervalos_semitonos">Fórmula de Intervalos en Semitonos *</label>
                    <span class="badge sensitive-badge">Validación Back-End</span>
                </div>
                <input id="intervalos_semitonos" name="intervalos_semitonos" type="text" value="{{ old('intervalos_semitonos', $tipoAcorde->intervalos_semitonos) }}" required>
                <p class="field-help text-sm text-muted">Debe comenzar en 0, incluir al menos 3 notas, estar en orden ascendente y no superar 24 semitonos.</p>
                @error('intervalos_semitonos') <p class="error alert-error">{{ $message }}</p> @enderror
            </div>

            <div class="mt-3">
                <label for="descripcion">Descripción Teórica</label>
                <textarea id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $tipoAcorde->descripcion) }}</textarea>
            </div>

            <div class="actions mt-4">
                <button type="submit" class="button large">💾 Actualizar Tipo de Acorde</button>
                <a href="{{ route('admin.tipos-acordes.index') }}" class="button secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
