@extends('layouts.app')

@section('title', 'Panel de Administración | ArmoníaLab')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">Administración del Sistema</span>
        <h1>Panel de Control Docente / Administrador</h1>
        <p class="subtitle">Gestión centralizada del Core musical, validación de datos sensibles y dropdowns dependientes.</p>
    </div>
    <div class="actions">
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.usuarios.create') }}" class="button small">➕ Nuevo Usuario</a>
        @endif
        <a href="{{ route('admin.ejercicios.create') }}" class="button secondary small">➕ Nuevo Ejercicio</a>
    </div>
</div>

<!-- Métricas de Administración -->
<div class="metrics-grid mb-4">
    @if(auth()->user()->isAdmin())
        <div class="card metric-card">
            <span class="metric-icon">👥</span>
            <div class="metric-info">
                <span class="metric-label">Usuarios Totales</span>
                <span class="metric-value">{{ $stats['total_usuarios'] }}</span>
            </div>
        </div>
    @endif

    <div class="card metric-card">
        <span class="metric-icon">📚</span>
        <div class="metric-info">
            <span class="metric-label">Cursos y Módulos</span>
            <span class="metric-value">{{ $stats['total_cursos'] }}</span>
        </div>
    </div>

    <div class="card metric-card">
        <span class="metric-icon">✍️</span>
        <div class="metric-info">
            <span class="metric-label">Ejercicios Activos</span>
            <span class="metric-value">{{ $stats['total_ejercicios'] }}</span>
        </div>
    </div>

    <div class="card metric-card">
        <span class="metric-icon">🎹</span>
        <div class="metric-info">
            <span class="metric-label">Tipos de Acordes</span>
            <span class="metric-value">{{ $stats['total_tipos_acordes'] }}</span>
        </div>
    </div>
</div>

<!-- Accesos a los Módulos Requeridos en la Entrega -->
<div class="admin-modules-grid mb-4">
    @if(auth()->user()->isAdmin())
        <div class="card admin-module-card">
            <div class="module-icon">🛡️</div>
            <h3>Gestión de Usuarios con Validación Back-End</h3>
            <p>Valida en el servidor datos sensibles (<strong>Cédula ecuatoriana con Módulo 10</strong>) y administra referencias mediante <strong>Dropdowns Dependientes en Cascada (País → Provincia → Ciudad)</strong>.</p>
            <div class="module-links mt-3">
                <a href="{{ route('admin.usuarios.index') }}" class="button small secondary">Listar Usuarios</a>
                <a href="{{ route('admin.usuarios.create') }}" class="button small">Registrar con Validación</a>
            </div>
        </div>
    @endif

    <div class="card admin-module-card">
        <div class="module-icon">📑</div>
        <h3>Creación de Ejercicios en Cascada</h3>
        <p>Formulario con selección dependiente: selecciona el <strong>Curso</strong> y automáticamente se carga el dropdown de <strong>Lecciones</strong> correspondientes mediante AJAX.</p>
        <div class="module-links mt-3">
            <a href="{{ route('admin.ejercicios.index') }}" class="button small secondary">Ver Ejercicios</a>
            <a href="{{ route('admin.ejercicios.create') }}" class="button small">Crear con Cascada</a>
        </div>
    </div>

    <div class="card admin-module-card">
        <div class="module-icon">🎼</div>
        <h3>Core Musical y Tipos de Acordes</h3>
        <p>Administra fórmulas de acordes con <strong>Validación Back-End</strong> de intervalos en semitonos (tónica obligatoria en 0, semitonos crecientes y coherencia armónica).</p>
        <div class="module-links mt-3">
            <a href="{{ route('admin.tipos-acordes.index') }}" class="button small secondary">Catálogo de Acordes</a>
            <a href="{{ route('admin.tipos-acordes.create') }}" class="button small">Nuevo Tipo de Acorde</a>
        </div>
    </div>
</div>

<!-- Últimos Usuarios Registrados y Actividad Reciente -->
<div class="{{ auth()->user()->isAdmin() ? 'grid-2-cols' : '' }}">
    @if(auth()->user()->isAdmin())
        <div class="card">
        <h3>Últimos Usuarios Registrados</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Cédula (Sensible)</th>
                        <th>Ubicación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ultimosUsuarios as $u)
                        <tr>
                            <td><strong>{{ $u->name }}</strong><br><small class="text-muted">{{ $u->email }}</small></td>
                            <td><span class="user-role-tag {{ $u->role }}">{{ ucfirst($u->role) }}</span></td>
                            <td><code>{{ $u->cedula ?: 'Sin cédula' }}</code></td>
                            <td>{{ $u->ciudad?->nombre ?? 'N/A' }}, {{ $u->provincia?->nombre ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>
    @endif

    <div class="card">
        <h3>Actividad Reciente de Estudiantes</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Ejercicio</th>
                        <th>Resultado</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ultimosIntentos as $intento)
                        <tr>
                            <td>{{ $intento->user?->name ?? 'Estudiante' }}</td>
                            <td>{{ Str::limit($intento->ejercicio?->pregunta ?? 'Ejercicio', 25) }}</td>
                            <td>
                                @if($intento->es_correcta)
                                    <span class="status-chip success">✓ Acierto</span>
                                @else
                                    <span class="status-chip retry">✗ Fallo</span>
                                @endif
                            </td>
                            <td>{{ $intento->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
