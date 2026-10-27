@extends('layouts.app')

@section('title', 'Catálogo de Tipos de Acordes | Admin ArmoníaLab')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">Core Musical MVC</span>
        <h1>Catálogo de Tipos de Acordes</h1>
        <p class="subtitle">Estructuras e intervalos armónicos utilizados por el Core de ArmoníaLab para el cálculo y visualización.</p>
    </div>
    <div class="actions">
        <a href="{{ route('admin.tipos-acordes.create') }}" class="button">➕ Nuevo Tipo de Acorde</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Símbolo</th>
                    <th>Categoría</th>
                    <th>Fórmula de Intervalos (Semitonos)</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tipos as $tipo)
                    <tr>
                        <td><strong>{{ $tipo->nombre }}</strong></td>
                        <td><span class="badge cipher-badge-sm">{{ $tipo->simbolo ?: 'M' }}</span></td>
                        <td><span class="badge-category">{{ $tipo->categoria }}</span></td>
                        <td><code class="formula-code">{{ $tipo->intervalos_semitonos }}</code></td>
                        <td class="text-sm text-muted">{{ Str::limit($tipo->descripcion, 60) }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.tipos-acordes.destroy', $tipo) }}" class="inline-form" onsubmit="return confirm('¿Eliminar este tipo de acorde?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button small danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
