@extends('layouts.app')
@section('title', 'Crear cuenta')
@section('content')
<section class="form-card narrow">
    <h1>Crear cuenta</h1>
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <label for="name">Nombre</label>
        <input id="name" name="name" value="{{ old('name') }}" required autofocus>
        @error('name') <p class="error">{{ $message }}</p> @enderror

        <label for="email">Correo electrónico</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required>
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required>
        @error('password') <p class="error">{{ $message }}</p> @enderror

        <label for="password_confirmation">Confirmar contraseña</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required>
        <button class="button full" type="submit">Crear cuenta</button>
    </form>
</section>
@endsection
