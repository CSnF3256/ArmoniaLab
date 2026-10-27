@extends('layouts.app')

@section('title', 'Gestión de Usuarios | Admin ArmoníaLab')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">Panel de Administración</span>
        <h1>Gestión de Usuarios y Roles</h1>
        <p class="subtitle">Usuarios registrados en ArmoníaLab con validación de datos sensibles (Cédula de Identidad) y ubicación geográfica en cascada.</p>
    </div>
    <div class="actions">
        <a href="{{ route('admin.usuarios.create') }}" class="button">➕ Registrar Nuevo Usuario</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Cédula (Dato Sensible)</th>
                    <th>País</th>
                    <th>Provincia</th>
                    <th>Ciudad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->name }}</strong>
                            <div class="text-sm text-muted">{{ $user->email }}</div>
                        </td>
                        <td>
                            <span class="user-role-tag {{ $user->role }}">{{ ucfirst($user->role) }}</span>
                        </td>
                        <td>
                            @if($user->cedula)
                                <code class="badge-cedula">{{ $user->cedula }}</code>
                                <span class="badge-verified" title="Validada con Módulo 10 en Servidor">✓ Validada</span>
                            @else
                                <span class="text-muted">No asignada</span>
                            @endif
                        </td>
                        <td>{{ $user->pais?->nombre ?? '—' }}</td>
                        <td>{{ $user->provincia?->nombre ?? '—' }}</td>
                        <td>{{ $user->ciudad?->nombre ?? '—' }}</td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('admin.usuarios.edit', $user) }}" class="button small secondary">Editar</a>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.usuarios.destroy', $user) }}" class="inline-form" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button small danger">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper mt-4">
        {{ $usuarios->links() }}
    </div>
</div>
@endsection
