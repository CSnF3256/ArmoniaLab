@extends('layouts.app')
@section('title', 'Crear cuenta | ArmoníaLab')

@section('content')
<section class="form-card narrow">
    <h1>Crear cuenta en ArmoníaLab</h1>
    <p class="subtitle">Regístrate para acceder al constructor de acordes, guardar tu biblioteca y resolver ejercicios con retroalimentación automática.</p>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <label for="name">Nombre Completo *</label>
        <input id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Tu nombre">
        @error('name') <p class="error">{{ $message }}</p> @enderror

        <label for="email">Correo electrónico *</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required placeholder="tucorreo@ejemplo.com">
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <div class="sensitive-box mt-3">
            <strong>🎓 Cuenta de estudiante</strong>
            <span class="text-sm text-muted">Las cuentas docentes y administrativas son asignadas de forma segura por un administrador.</span>
        </div>

        <div class="sensitive-box mt-3 mb-2">
            <label for="cedula">Cédula Ecuatoriana (Dato Sensible Opcional)</label>
            <input id="cedula" name="cedula" type="text" maxlength="10" value="{{ old('cedula') }}" placeholder="Ej: 1710034065">
            <span class="text-sm text-muted">Se valida mediante Módulo 10 en el Back-End antes de guardarse.</span>
            @error('cedula') <p class="error">{{ $message }}</p> @enderror
        </div>

        <label for="password">Contraseña *</label>
        <input id="password" name="password" type="password" required placeholder="Mínimo 8 caracteres">
        @error('password') <p class="error">{{ $message }}</p> @enderror

        <label for="password_confirmation">Confirmar contraseña *</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Repite tu contraseña">

        <button class="button full mt-4" type="submit">Crear cuenta en ArmoníaLab</button>

        <p class="form-footer mt-3">
            ¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="link-small">Inicia sesión aquí</a>
        </p>
    </form>
</section>
@endsection
