<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', 'GameWordle — guess the video game by its attributes or screenshot.')">

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('og_title', config('app.name'))">
    <meta property="og:description" content="@yield('og_description', 'Guess the video game by its attributes or screenshot.')">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif

    <link rel="canonical" href="{{ url()->current() }}">

    @stack('structured_data')

    @vite(['resources/css/app.css'])
</head>
<body class="antialiased">
    <header>
        <nav>
            <a href="{{ url('/') }}">{{ config('app.name') }}</a>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </footer>
</body>
</html>
