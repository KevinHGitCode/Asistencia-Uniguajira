<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

    <!-- Contenido principal sin sidebar -->
    <div class="flex flex-col min-h-screen">
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>

    @fluxScripts
</body>

</html>
