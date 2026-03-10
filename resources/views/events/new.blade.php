<x-layouts.app :title="__('Nuevo evento')">
    <div class="px-2 pt-2 pb-6">
        {{-- centrar el titulo direccion columna --}}
        <div class="flex flex-col items-center mb-8">
            <h1 class="text-xl font-bold text-white">Crea un nuevo evento</h1>
            <p class="text-sm text-gray-300">Sigue los pasos para configurar tu evento</p>
        </div>
        <livewire:event.create-event-wizard />
    </div>
</x-layouts.app>
