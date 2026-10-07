@extends('layouts.app')
@section('title', 'Editar curso')
@section('content')
<section class="form-card"><h1>Editar curso</h1><form method="POST" action="{{ route('cursos.update', $course) }}">@include('courses._form', ['buttonText' => 'Actualizar curso'])</form></section>
@endsection
