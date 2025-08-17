<x-layouts.app :title="__('New')">
    <div>
        <p class="text-2xl font-bold text-gray-800 mb-4"> Nuevo evento </p>

        <div>
            <form wire:submit="createEvent" class="flex flex-col gap-6">
                <flux:input wire:model="eventName" :label="__('Nombre del evento')" type="text" required autofocus
                    placeholder="Día del amor y la amistad" />

                <flux:input wire:model="description" :label="__('Descripción del evento')" type="text" required
                    placeholder="Evento especial para celebrar el amor y la amistad" />

                <flux:input wire:model="date" :label="__('Fecha del evento')" type="date" required />

                <flux:input wire:model="startTime" :label="__('Hora de inicio')" type="time" required />

                <flux:input wire:model="endTime" :label="__('Hora de finalización')" type="time" required />

                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('Crear Evento') }}
                </flux:button>
            </form>
        </div>
    </div>
</x-layouts.app>
