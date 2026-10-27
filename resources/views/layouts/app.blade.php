<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ArmoníaLab - Plataforma de Teoría Musical')</title>
    <link rel="stylesheet" href="/css/app.css?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="site-header">
        <nav class="container nav">
            <div class="brand-group">
                <a class="brand" href="{{ route('home') }}">
                    <span class="brand-icon">🎵</span> ArmoníaLab
                </a>
                <span class="brand-tag">Core MVC Musical</span>
            </div>

            <div class="nav-links">
                <a href="{{ route('acordes.index') }}" class="{{ request()->routeIs('acordes.index') ? 'active' : '' }}">🎹 Constructor de Acordes</a>

                @auth
                    <a href="{{ route('acordes.biblioteca') }}" class="{{ request()->routeIs('acordes.biblioteca') ? 'active' : '' }}">📚 Biblioteca</a>
                    <a href="{{ route('ejercicios.index') }}" class="{{ request()->routeIs('ejercicios.*') ? 'active' : '' }}">✍️ Ejercicios</a>
                    <a href="{{ route('progreso.index') }}" class="{{ request()->routeIs('progreso.*') ? 'active' : '' }}">📊 Mi Progreso</a>
                    <a href="{{ route('cursos.index') }}" class="{{ request()->routeIs('cursos.*') ? 'active' : '' }}">🎓 Cursos</a>

                    @if(auth()->user()->isAdmin() || auth()->user()->isDocente())
                        <a href="{{ route('admin.dashboard') }}" class="admin-badge-nav {{ request()->routeIs('admin.*') ? 'active' : '' }}">⚙️ Panel Admin</a>
                    @endif

                    <div class="user-pill">
                        <span class="user-role-tag {{ auth()->user()->role }}">{{ ucfirst(auth()->user()->role) }}</span>
                        <span class="user-name">{{ auth()->user()->name }}</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline-form">
                        @csrf
                        <button class="link-button logout-btn" type="submit" title="Cerrar sesión">Salir</button>
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
            <div class="alert success">
                <span class="alert-icon">✓</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('resultado_exito'))
            <div class="alert success">
                <span class="alert-icon">🎉</span>
                <div>{{ session('resultado_exito') }}</div>
            </div>
        @endif

        @if (session('resultado_error'))
            <div class="alert danger">
                <span class="alert-icon">⚠️</span>
                <div>{{ session('resultado_error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert danger">
                <span class="alert-icon">❌</span>
                <div>
                    <strong>Por favor corrige los siguientes errores:</strong>
                    <ul class="error-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-content">
            <p><strong>ArmoníaLab</strong> — Plataforma de aprendizaje de teoría musical y visualización armónica.</p>
            <p class="footer-meta">UDLA | ITIZ-2201 Ingeniería Web | Proyecto de Análisis y Core MVC | Camila Silva & Joel Chico</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
