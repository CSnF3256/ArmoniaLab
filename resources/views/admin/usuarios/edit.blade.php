@extends('layouts.app')

@section('title', 'Editar Usuario | Admin ArmoníaLab')

@section('content')
<div class="form-container">
    <div class="breadcrumb mb-3">
        <a href="{{ route('admin.usuarios.index') }}">← Volver a Usuarios</a>
    </div>

    <div class="card form-card">
        <div class="form-header mb-4">
            <span class="eyebrow">Edición de Perfil & Datos Sensibles</span>
            <h1>Editar Usuario: {{ $usuario->name }}</h1>
        </div>

        <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div>
                    <label for="name">Nombre Completo *</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $usuario->name) }}" required>
                    @error('name') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email">Correo Electrónico *</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $usuario->email) }}" required>
                    @error('email') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-grid mt-3">
                <div>
                    <label for="password">Nueva Contraseña (dejar en blanco para conservar)</label>
                    <input id="password" name="password" type="password" placeholder="Opcional">
                    @error('password') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="role">Rol del Usuario *</label>
                    <select id="role" name="role" required>
                        <option value="estudiante" @selected(old('role', $usuario->role) === 'estudiante')>Estudiante</option>
                        <option value="docente" @selected(old('role', $usuario->role) === 'docente')>Docente</option>
                        <option value="admin" @selected(old('role', $usuario->role) === 'admin')>Administrador</option>
                    </select>
                    @error('role') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- CAMPO SENSIBLE CON VALIDACIÓN BACK-END OBLIGATORIA -->
            <div class="sensitive-box mt-4">
                <div class="sensitive-label-group">
                    <label for="cedula">Cédula de Identidad Ecuatoriana (Dato Sensible) *</label>
                    <span class="badge sensitive-badge">Validación Módulo 10 en Back-End</span>
                </div>
                <input id="cedula" name="cedula" type="text" maxlength="10" value="{{ old('cedula', $usuario->cedula) }}" required>
                @error('cedula') <p class="error alert-error">{{ $message }}</p> @enderror
            </div>

            <!-- DROPDOWNS DEPENDIENTES (CASCADA) -->
            <div class="cascading-section mt-4">
                <h3>Ubicación Geográfica (Dropdowns en Cascada)</h3>

                <div class="form-grid-3">
                    <div>
                        <label for="pais_id">1. País *</label>
                        <select id="pais_id" name="pais_id" required onchange="cargarProvincias(this.value)">
                            <option value="">-- Seleccionar País --</option>
                            @foreach($paises as $pais)
                                <option value="{{ $pais->id }}" @selected(old('pais_id', $usuario->pais_id) == $pais->id)>
                                    {{ $pais->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('pais_id') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="provincia_id">2. Provincia *</label>
                        <select id="provincia_id" name="provincia_id" required onchange="cargarCiudades(this.value)">
                            <option value="">-- Seleccionar Provincia --</option>
                            @foreach($provincias as $prov)
                                <option value="{{ $prov->id }}" @selected(old('provincia_id', $usuario->provincia_id) == $prov->id)>
                                    {{ $prov->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('provincia_id') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="ciudad_id">3. Ciudad *</label>
                        <select id="ciudad_id" name="ciudad_id" required>
                            <option value="">-- Seleccionar Ciudad --</option>
                            @foreach($ciudades as $ciu)
                                <option value="{{ $ciu->id }}" @selected(old('ciudad_id', $usuario->ciudad_id) == $ciu->id)>
                                    {{ $ciu->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('ciudad_id') <p class="error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="actions mt-5">
                <button type="submit" class="button large">💾 Actualizar Usuario</button>
                <a href="{{ route('admin.usuarios.index') }}" class="button secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function cargarProvincias(paisId) {
    const selectProvincia = document.getElementById('provincia_id');
    const selectCiudad = document.getElementById('ciudad_id');

    selectProvincia.innerHTML = '<option value="">Cargando provincias...</option>';
    selectProvincia.disabled = true;
    selectCiudad.innerHTML = '<option value="">-- Primero elija una provincia --</option>';
    selectCiudad.disabled = true;

    if (!paisId) return;

    try {
        const res = await fetch(`/api/paises/${paisId}/provincias`);
        const provincias = await res.json();

        selectProvincia.innerHTML = '<option value="">-- Seleccionar Provincia --</option>';
        provincias.forEach(prov => {
            const opt = document.createElement('option');
            opt.value = prov.id;
            opt.textContent = prov.nombre;
            selectProvincia.appendChild(opt);
        });
        selectProvincia.disabled = false;
    } catch (e) {
        console.error(e);
    }
}

async function cargarCiudades(provinciaId) {
    const selectCiudad = document.getElementById('ciudad_id');
    selectCiudad.innerHTML = '<option value="">Cargando ciudades...</option>';
    selectCiudad.disabled = true;

    if (!provinciaId) return;

    try {
        const res = await fetch(`/api/provincias/${provinciaId}/ciudades`);
        const ciudades = await res.json();

        selectCiudad.innerHTML = '<option value="">-- Seleccionar Ciudad --</option>';
        ciudades.forEach(ciu => {
            const opt = document.createElement('option');
            opt.value = ciu.id;
            opt.textContent = ciu.nombre;
            selectCiudad.appendChild(opt);
        });
        selectCiudad.disabled = false;
    } catch (e) {
        console.error(e);
    }
}
</script>
@endpush
