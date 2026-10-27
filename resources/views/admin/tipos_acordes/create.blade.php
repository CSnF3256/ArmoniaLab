@extends('layouts.app')

@section('title', 'Nuevo Tipo de Acorde | Admin ArmoníaLab')

@section('content')
<div class="form-container">
    <div class="breadcrumb mb-3">
        <a href="{{ route('admin.tipos-acordes.index') }}">← Volver a Tipos de Acordes</a>
    </div>

    <div class="card form-card">
        <div class="form-header mb-4">
            <span class="eyebrow">Validación de Datos Sensibles del Core Musical</span>
            <h1>Registrar Nuevo Tipo de Acorde</h1>
            <p class="subtitle">La estructura de semitonos es un <strong>dato sensible del Core armónico</strong>: si se almacena una fórmula errónea, el cálculo y el teclado virtual mostrarán notas incorrectas a los alumnos. Por ello, se valida estrictamente en el <strong>Back-End</strong> mediante la regla <code>ValidIntervalStructure</code>.</p>
        </div>

        <form method="POST" action="{{ route('admin.tipos-acordes.store') }}">
            @csrf

            <div class="form-grid">
                <div>
                    <label for="nombre">Nombre del Acorde *</label>
                    <input id="nombre" name="nombre" type="text" value="{{ old('nombre') }}" placeholder="Ej: Séptima de Dominante, Menor 9, etc." required>
                    @error('nombre') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="simbolo">Símbolo o Cifrado Armónico *</label>
                    <input id="simbolo" name="simbolo" type="text" value="{{ old('simbolo') }}" placeholder="Ej: 7, maj7, m, dim, aug" required>
                    @error('simbolo') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-3">
                <label for="categoria">Categoría Armónica *</label>
                <select id="categoria" name="categoria" required>
                    <option value="Tríadas" @selected(old('categoria') === 'Tríadas')>Tríadas (3 notas)</option>
                    <option value="Séptimas" @selected(old('categoria') === 'Séptimas')>Séptimas (4 notas)</option>
                    <option value="Suspendidos" @selected(old('categoria') === 'Suspendidos')>Suspendidos (Sus2, Sus4)</option>
                    <option value="Alterados" @selected(old('categoria') === 'Alterados')>Alterados (Disminuidos, Aumentados)</option>
                    <option value="Extendidos" @selected(old('categoria') === 'Extendidos')>Extendidos (Novenas, Oncenas)</option>
                </select>
            </div>

            <!-- CAMPO SENSIBLE DEL CORE VALIDADO EN BACK-END -->
            <div class="sensitive-box mt-4">
                <div class="sensitive-label-group">
                    <label for="intervalos_semitonos">Fórmula de Intervalos en Semitonos (Dato Sensible del Core) *</label>
                    <span class="badge sensitive-badge">Validación Matemática en Back-End</span>
                </div>
                <input id="intervalos_semitonos" name="intervalos_semitonos" type="text" value="{{ old('intervalos_semitonos') }}" placeholder="Ej: 0, 4, 7 o [0, 4, 7, 10]" required>

                <div class="field-help text-sm text-muted mt-2">
                    <strong>Reglas validadas por el servidor antes de guardar:</strong>
                    <ul class="text-sm mt-1">
                        <li>Debe comenzar obligatoriamente en <code>0</code> (la tónica / nota fundamental).</li>
                        <li>Debe contener al menos 3 notas para constituir un acorde armónico.</li>
                        <li>Los semitonos deben ser números enteros estrictamente ascendentes (ej: <code>0, 4, 7</code>).</li>
                        <li>Ningún intervalo puede superar los 24 semitonos (límite de 2 octavas).</li>
                    </ul>
                </div>
                @error('intervalos_semitonos') <p class="error alert-error">{{ $message }}</p> @enderror
            </div>

            <div class="mt-3">
                <label for="descripcion">Descripción Teórica / Explicación del Acorde</label>
                <textarea id="descripcion" name="descripcion" rows="3" placeholder="Ej: Acorde de cuatro sonidos caracterizado por su tensión armónica que busca resolver al grado I.">{{ old('descripcion') }}</textarea>
            </div>

            <div class="actions mt-4">
                <button type="submit" class="button large">💾 Guardar Tipo de Acorde con Validación</button>
                <a href="{{ route('admin.tipos-acordes.index') }}" class="button secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
