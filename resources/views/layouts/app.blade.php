<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Такси Сервис - @yield('title')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Yandex Maps -->
    <script src="https://api-maps.yandex.ru/2.1/?apikey=ваш_апи_ключ&lang=ru_RU" type="text/javascript"></script>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">Такси Сервис</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    @auth
                        @if(auth()->user()->role === 'client')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('client.dashboard') }}">Заказать такси</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('client.orders') }}">Мои заказы</a>
                            </li>
                        @elseif(auth()->user()->role === 'driver')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('driver.dashboard') }}">Панель водителя</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('driver.orders') }}">Мои заказы</a>
                            </li>
                        @elseif(auth()->user()->role === 'admin')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">Панель администратора</a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link">Выйти</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Войти</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html> 