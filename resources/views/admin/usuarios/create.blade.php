@extends('layouts.app')

@section('title', 'Registrar Usuario con Validación Back-End | Admin ArmoníaLab')

@section('content')
<div class="form-container">
    <div class="breadcrumb mb-3">
        <a href="{{ route('admin.usuarios.index') }}">← Volver a Usuarios</a>
    </div>

    <div class="card form-card">
        <div class="form-header mb-4">
            <span class="eyebrow">Validación Sensible & Dropdowns en Cascada</span>
            <h1>Registrar Nuevo Usuario</h1>
            <p class="subtitle">Este formulario cumple con los dos requerimientos de validación de arquitectura:</p>

            <div class="compliance-box mt-3">
                <div class="compliance-item">
                    <span class="check-icon">🛡️</span>
                    <div>
                        <strong>1. Validación en Back-End de Dato Sensible:</strong>
                        <p class="text-sm">La <strong>Cédula de Identidad</strong> no se valida únicamente en JS, sino mediante la regla <code>ValidEcuadorianCedula</code> en Laravel (algoritmo de Módulo 10, validación de código de provincia 01-24 y tercer dígito) antes de persistir en base de datos.</p>
                    </div>
                </div>
                <div class="compliance-item mt-2">
                    <span class="check-icon">🔗</span>
                    <div>
                        <strong>2. Dropdowns Dependientes en Referencias:</strong>
                        <p class="text-sm">Para asignar la ubicación (<strong>País → Provincia → Ciudad</strong>), no se ingresan claves foráneas manualmente en inputs simples. Se utilizan dropdowns en cascada donde al elegir el país se cargan dinámicamente sus provincias, y al elegir la provincia se cargan sus ciudades.</p>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.usuarios.store') }}" id="user-form">
            @csrf

            <div class="form-grid">
                <div>
                    <label for="name">Nombre Completo *</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Ej: Joel Chico" required>
                    @error('name') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email">Correo Institucional / Personal *</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="ejemplo@udla.edu.ec" required>
                    @error('email') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-grid mt-3">
                <div>
                    <label for="password">Contraseña *</label>
                    <input id="password" name="password" type="password" placeholder="Mínimo 8 caracteres" required>
                    @error('password') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="role">Rol del Usuario *</label>
                    <select id="role" name="role" required>
                        <option value="estudiante" @selected(old('role') === 'estudiante')>Estudiante</option>
                        <option value="docente" @selected(old('role') === 'docente')>Docente</option>
                        <option value="admin" @selected(old('role') === 'admin')>Administrador</option>
                    </select>
                    @error('role') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- CAMPO SENSIBLE CON VALIDACIÓN BACK-END OBLIGATORIA -->
            <div class="sensitive-box mt-4">
                <div class="sensitive-label-group">
                    <label for="cedula">Cédula de Identidad Ecuatoriana (Dato Sensible del Core) *</label>
                    <span class="badge sensitive-badge">Validación Módulo 10 en Back-End</span>
                </div>
                <input id="cedula" name="cedula" type="text" maxlength="10" value="{{ old('cedula') }}" placeholder="Ej: 1710034065 o 0915284392" required>
                <p class="field-help text-sm text-muted">Se verificará en el servidor antes de guardar. Cédulas con dígito verificador falso o provincia inválida serán rechazadas por el controlador.</p>
                @error('cedula') <p class="error alert-error">{{ $message }}</p> @enderror
            </div>

            <!-- DROPDOWNS DEPENDIENTES (CASCADA) PAÍS -> PROVINCIA -> CIUDAD -->
            <div class="cascading-section mt-4">
                <h3>Ubicación Geográfica (Dropdowns en Cascada)</h3>
                <p class="text-sm text-muted mb-3">Las claves foráneas de País, Provincia y Ciudad se obtienen mediante selección dependiente dinámica:</p>

                <div class="form-grid-3">
                    <div>
                        <label for="pais_id">1. País *</label>
                        <select id="pais_id" name="pais_id" required onchange="cargarProvincias(this.value)">
                            <option value="">-- Seleccionar País --</option>
                            @foreach($paises as $pais)
                                <option value="{{ $pais->id }}" @selected(old('pais_id') == $pais->id)>
                                    {{ $pais->nombre }} ({{ $pais->codigo }})
                                </option>
                            @endforeach
                        </select>
                        @error('pais_id') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="provincia_id">2. Provincia / Estado *</label>
                        <select id="provincia_id" name="provincia_id" required disabled onchange="cargarCiudades(this.value)">
                            <option value="">-- Primero elija un país --</option>
                        </select>
                        @error('provincia_id') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="ciudad_id">3. Ciudad / Cantón *</label>
                        <select id="ciudad_id" name="ciudad_id" required disabled>
                            <option value="">-- Primero elija una provincia --</option>
                        </select>
                        @error('ciudad_id') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="actions mt-5">
                <button type="submit" class="button large">💾 Guardar Usuario con Validación</button>
                <a href="{{ route('admin.usuarios.index') }}" class="button secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Manejo dinámico de Dropdowns en Cascada mediante AJAX (Fetch API)
const oldProvinciaId = "{{ old('provincia_id') }}";
const oldCiudadId = "{{ old('ciudad_id') }}";

async function cargarProvincias(paisId, preseleccionarId = null) {
    const selectProvincia = document.getElementById('provincia_id');
    const selectCiudad = document.getElementById('ciudad_id');

    selectProvincia.innerHTML = '<option value="">Cargando provincias...</option>';
    selectProvincia.disabled = true;
    selectCiudad.innerHTML = '<option value="">-- Primero elija una provincia --</option>';
    selectCiudad.disabled = true;

    if (!paisId) {
        selectProvincia.innerHTML = '<option value="">-- Primero elija un país --</option>';
        return;
    }

    try {
        const res = await fetch(`/api/paises/${paisId}/provincias`);
        const provincias = await res.json();

        selectProvincia.innerHTML = '<option value="">-- Seleccionar Provincia --</option>';
        provincias.forEach(prov => {
            const opt = document.createElement('option');
            opt.value = prov.id;
            opt.textContent = prov.nombre;
            if (preseleccionarId && String(preseleccionarId) === String(prov.id)) {
                opt.selected = true;
            }
            selectProvincia.appendChild(opt);
        });

        selectProvincia.disabled = false;

        // Si venía un valor previo (por error de validación)
        if (preseleccionarId) {
            cargarCiudades(preseleccionarId, oldCiudadId);
        }
    } catch (e) {
        selectProvincia.innerHTML = '<option value="">Error al cargar provincias</option>';
        console.error(e);
    }
}

async function cargarCiudades(provinciaId, preseleccionarId = null) {
    const selectCiudad = document.getElementById('ciudad_id');

    selectCiudad.innerHTML = '<option value="">Cargando ciudades...</option>';
    selectCiudad.disabled = true;

    if (!provinciaId) {
        selectCiudad.innerHTML = '<option value="">-- Primero elija una provincia --</option>';
        return;
    }

    try {
        const res = await fetch(`/api/provincias/${provinciaId}/ciudades`);
        const ciudades = await res.json();

        selectCiudad.innerHTML = '<option value="">-- Seleccionar Ciudad --</option>';
        ciudades.forEach(ciu => {
            const opt = document.createElement('option');
            opt.value = ciu.id;
            opt.textContent = ciu.nombre;
            if (preseleccionarId && String(preseleccionarId) === String(ciu.id)) {
                opt.selected = true;
            }
            selectCiudad.appendChild(opt);
        });

        selectCiudad.disabled = false;
    } catch (e) {
        selectCiudad.innerHTML = '<option value="">Error al cargar ciudades</option>';
        console.error(e);
    }
}

// Inicializar si hay valores antiguos (old input)
document.addEventListener('DOMContentLoaded', () => {
    const paisInicial = document.getElementById('pais_id').value;
    if (paisInicial) {
        cargarProvincias(paisInicial, oldProvinciaId);
    }
});
</script>
@endpush
