<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@auth
    <meta name="user-dependency-id" content="{{ auth()->user()->dependency_id }}">
    <meta name="user-id" content="{{ auth()->user()->id }}">
    <meta name="user-role" content="{{ auth()->user()->role }}">
@endauth

<title>{{ $title ?? config('app.name') }}</title>

<!-- Iconos de la aplicación -->
<link rel="icon" href="{{ asset('images/favicon-uniguajira-32x32.webp') }}" sizes="any">
<link rel="icon" href="{{ asset('images/favicon-uniguajira-32x32.webp') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('images/favicon-uniguajira-32x32.webp') }}">

<!-- Fuentes de bunny -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@viteReactRefresh
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

{{-- Scripts y estilos específicos de cada página (ej: Cal-Heatmap en el dashboard) --}}
@stack('head-scripts')
