<x-layouts.app :title="__('New')">
    <div>
        <p class="text-2xl font-bold text-white mb-4"> Nuevo evento </p>

        <div class="flex justify-start">
            <form wire:submit="{{route('events.new')}}" class="flex flex-col gap-6">

                <flux:input wire:model="eventName" :label="__('Nombre del evento')" type="text" required autofocus
                    placeholder="Día del amor y la amistad" />

                <flux:input wire:model="description" :label="__('Descripción del evento')" type="text" required
                    placeholder="Evento especial para celebrar el amor y la amistad" />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
                    <flux:input wire:model="date" :label="__('Fecha del evento')" type="date" required />

                    <flux:input wire:model="startTime" :label="__('Hora de inicio')" type="time" required />

                    <flux:input wire:model="endTime" :label="__('Hora de finalización')" type="time" required />
                </div>

                <div class="flex justify-start">
                    <flux:button variant="primary" type="submit" class="px-3 py-6">
                        {{ __('Crear Evento') }}
                    </flux:button>
                </div>
                
            </form>
        </div>
    </div>
</x-layouts.app>