@php
    $accent = collect(['#cc5e50', '#e2a542', '#62a9b6'])->random();
@endphp

<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main class="pattern-bg" style="--accent: {{ $accent }}">
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>