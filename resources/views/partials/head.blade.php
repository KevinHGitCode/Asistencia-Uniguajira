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

<!-- importacion de `Cal-Heatmap`: gráficos tipo calendario y librerias auxiliares -->
<script src="https://d3js.org/d3.v6.min.js"></script>
<script src="https://unpkg.com/cal-heatmap/dist/cal-heatmap.min.js"></script>
{{-- <script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/zh-cn.js"></script>  <!-- esto da error --> --}}
<link rel="stylesheet" href="https://unpkg.com/cal-heatmap/dist/cal-heatmap.css">

<!-- importacion de `Apache Echarts` para graficos -->
<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>



@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/calendar.css', 'resources/js/calendar/index.js'])
@fluxAppearance
