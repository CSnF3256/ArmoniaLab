@extends('layouts.app')
@section('title', 'Nuevo curso')
@section('content')
<section class="form-card"><h1>Crear curso</h1><form method="POST" action="{{ route('cursos.store') }}">@include('courses._form', ['buttonText' => 'Guardar curso'])</form></section>
@endsection
