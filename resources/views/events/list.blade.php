<x-layouts.app :title="__('List')">
    <div>
        <h1 class="text-2xl font-bold mb-4"> Tus eventos </h1>
    </div>

    <div class="mb-4">
        <flux:dropdown>
            <flux:button icon:leading="chevron-down">Filtrar</flux:button>

            <flux:menu keep-open>
                <flux:menu.checkbox checked>Todo</flux:menu.checkbox>
                <flux:menu.separator></flux:menu.separator>
                <flux:menu.checkbox>Por fecha</flux:menu.checkbox>
                <flux:menu.checkbox>Proximos</flux:menu.checkbox>
                <flux:menu.checkbox>Pasados</flux:menu.checkbox>
                <flux:menu.checkbox>Actuales</flux:menu.checkbox>
            </flux:menu>

        </flux:dropdown>
    </div>

    <div class="relative flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        @if ($events->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
                @foreach ($events as $event)
                    <a href="{{ route('events.show', $event->id) }}" class="block hover:shadow-lg hover:scale-102 transition-transform duration-200">
                        <livewire:event.card
                            :title="$event->title" 
                            :date="$event->date" 
                            :location="$event->location ?? 'Sin ubicación'" 
                            :description="$event->description ?? 'Sin descripción'"
                            :start_time="$event->start_time"
                            :end_time="$event->end_time" />
                    </a>
                @endforeach
            </div>
        @else
            <!-- Mensaje cuando no hay eventos -->
            <div class="flex flex-col items-center justify-center h-64 text-gray-500 dark:text-white">
                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v8a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z">
                    </path>
                </svg>
                <h3 class="text-xl font-semibold mb-2">No tienes eventos creados</h3>
                <p class="text-center mb-4">¡Crea tu primer evento para comenzar a organizar!</p>
                <a href="{{ route('events.new') }}"
                    class="px-4 py-2 bg-dark dark:bg-white text-white dark:text-black rounded-lg hover:bg-gray-300 hover:scale-105 transition-colors transition-transform">
                    Crear nuevo evento
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>
