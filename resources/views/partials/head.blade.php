<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="{{ asset('images/favicon-uniguajira-32x32.webp') }}" sizes="any">
<link rel="icon" href="{{ asset('images/favicon-uniguajira-32x32.webp') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('images/favicon-uniguajira-32x32.webp') }}">
<script src="https://d3js.org/d3.v6.min.js"></script>
<script src="https://unpkg.com/cal-heatmap/dist/cal-heatmap.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/cal-heatmap/dist/cal-heatmap.css">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
