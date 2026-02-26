<x-layouts.app :title="__('Estadísticas Generales')">
    <flux:heading size="xl" level="1" class="mb-3">Estadísticas Generales</flux:heading>
    <flux:subheading size="lg" class="mb-6">
        {{ __('Visualiza un resumen general de la actividad, usuarios y eventos del sistema.') }}
    </flux:subheading>

    {{-- Punto de montaje de la app React de estadísticas --}}
    <div id="statistics-react-root"></div>

    @vite(['resources/js/statistics/index.jsx'])
</x-layouts.app>
