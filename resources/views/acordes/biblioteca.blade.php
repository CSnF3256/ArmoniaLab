@extends('layouts.app')

@section('title', 'Mi Biblioteca de Acordes | ArmoníaLab')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">Colección del Estudiante</span>
        <h1>Mi Biblioteca de Acordes Guardados</h1>
        <p class="subtitle">Consulta los acordes que has analizado y guardado para tus prácticas y análisis armónico.</p>
    </div>
    <div class="actions">
        <a href="{{ route('acordes.index') }}" class="button">🎹 Construir Nuevo Acorde</a>
    </div>
</div>

@if($acordes->isEmpty())
    <div class="card empty">
        <div class="empty-icon">🎼</div>
        <h2>Aún no has guardado acordes</h2>
        <p>Utiliza el constructor interactivo para calcular acordes y guárdalos en tu biblioteca con un solo click.</p>
        <a href="{{ route('acordes.index') }}" class="button mt-3">Ir al Constructor de Acordes</a>
    </div>
@else
    <div class="library-grid">
        @foreach($acordes as $item)
            <div class="card chord-saved-card">
                <div class="chord-saved-header">
                    <div>
                        <span class="badge-category">{{ $item->tipoAcorde?->categoria ?? 'Acorde' }}</span>
                        <h3>{{ $item->nombre_completo }}</h3>
                        <span class="cipher">{{ $item->fundamental }}{{ $item->tipoAcorde?->simbolo === 'M' ? '' : $item->tipoAcorde?->simbolo }}</span>
                    </div>
                </div>

                <div class="notes-badges mt-3">
                    @php $notas = is_array($item->notas) ? $item->notas : json_decode($item->notas, true) ?? []; @endphp
                    @foreach($notas as $nota)
                        <span class="note-tag">{{ $nota }}</span>
                    @endforeach
                </div>

                <div class="card-footer-actions mt-4">
                    <a href="{{ route('acordes.index', ['fundamental' => $item->fundamental, 'tipo_id' => $item->tipo_acorde_id]) }}" class="button small">
                        🔍 Ver en Teclado
                    </a>

                    <form method="POST" action="{{ route('acordes.destroy', $item) }}" class="inline-form" onsubmit="return confirm('¿Seguro que deseas eliminar este acorde de tu biblioteca?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="button small danger">Eliminar</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
