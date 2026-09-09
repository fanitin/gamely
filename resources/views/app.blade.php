<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('seo.title') }}</title>
    <meta name="description" content="{{ config('seo.description') }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Gamely">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ config('seo.title') }}">
    <meta property="og:description" content="{{ config('seo.description') }}">
    <meta property="og:image" content="{{ url(config('seo.image')) }}">
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('seo.title') }}">
    <meta name="twitter:description" content="{{ config('seo.description') }}">
    <meta name="twitter:image" content="{{ url(config('seo.image')) }}">

    <link rel="icon" type="image/png" href="/favicons/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicons/favicon.svg" />
    <link rel="shortcut icon" href="/favicons/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Gamely" />
    <link rel="manifest" href="/favicons/site.webmanifest" />

    <link rel="preload" as="font" type="font/woff2" href="/fonts/inter-latin.woff2" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="/fonts/DepartureMono-Regular.woff2" crossorigin>
    <link rel="preload" as="image" href="/images/background/main-bg.jpg" fetchpriority="high">

    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }
        gtag('consent', 'default', {
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            analytics_storage: 'denied',
            wait_for_update: 500
        });
    </script>
    @routes
    @inertiaHead
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    @inertia
</body>
</html>
