<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">
    <div id="app">
        <nav class="bg-white shadow mb-4">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center py-4">
                    <div>
                        <a href="{{ url('/') }}" class="text-lg font-semibold text-gray-900">
                            {{ config('app.name', 'Laravel') }}
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('users.index') }}" class="mx-2 text-gray-700 hover:text-gray-900 {{ request()->routeIs('users.index') ? 'nav-link-active' : '' }}">Users</a>
                        <a href="{{ route('daily-records.index') }}" class="mx-2 text-gray-700 hover:text-gray-900 {{ request()->routeIs('daily-records.index') ? 'nav-link-active' : '' }}">Daily Records</a>
                    </div>
                </div>
            </div>
        </nav>

        <main class="container mx-auto px-4">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
