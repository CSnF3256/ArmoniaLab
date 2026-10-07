<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ArmoníaLab')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <nav class="container nav">
            <a class="brand" href="{{ route('home') }}">ArmoníaLab</a>
            <div class="nav-links">
                @auth
                    <a href="{{ route('cursos.index') }}">Mis cursos</a>
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="link-button" type="submit">Cerrar sesión</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Iniciar sesión</a>
                    <a class="button small" href="{{ route('register') }}">Crear cuenta</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="container main-content">
        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
