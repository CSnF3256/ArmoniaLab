@extends('layouts.app')
@section('title', 'Iniciar sesión')
@section('content')
<section class="form-card narrow">
    <h1>Iniciar sesión</h1>
    <p>Accede a la sección protegida de cursos.</p>
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label for="email">Correo electrónico</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required>
        @error('password') <p class="error">{{ $message }}</p> @enderror

        <div style="margin: 1rem 0; display: flex; align-items: center;">
            <label for="remember_me" style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input id="remember_me" type="checkbox" name="remember" style="width: 1.1rem; height: 1.1rem; accent-color: var(--purple); cursor: pointer;">
                <span style="font-size: 0.9rem; font-weight: 500; color: var(--ink);">Recordarme</span>
            </label>
        </div>

        <button class="button full" type="submit">Ingresar</button>
    </form>
    <p class="form-footer">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a>.</p>
</section>
@endsection
