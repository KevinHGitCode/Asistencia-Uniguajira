<x-layouts.app :title="__('Evento')">

    <nav class="text-sm mb-4 text-gray-500 dark:text-gray-300" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li><a href="{{ route('events.list') }}" class="hover:underline"> Eventos </a></li>
            <li><span class="mx-2">/</span></li>
            <li class="font-bold text-gray-900 dark:text-white"> Información </li>
        </ol>
    </nav>

    <div class="relative flex h-full w-full flex-1 flex-col gap-4 p-2 rounded-xl border border-neutral-200 dark:border-neutral-700">

        <div class="p-4 overflow-hidden rounded-xl">

            <h1 class="text-3xl font-bold mb-4">Detalles del Evento</h1>   

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div class="mb-4 border border-neutral-200 dark:border-neutral-700 p-4 rounded-lg">
                    <p class="text-lg mb-2"><strong>Título:</strong> {{ $event->title }}</p>
                    <p class="text-lg mb-2"><strong>Fecha:</strong> {{ $event->date }}</p>
                    <p class="text-lg mb-2"><strong>Hora de Inicio:</strong> {{ $event->start_time }}</p>
                    <p class="text-lg mb-2"><strong>Hora de Fin:</strong> {{ $event->end_time }}</p>
                    <p class="text-lg mb-2"><strong>Ubicación:</strong> {{ $event->location ?? 'Sin ubicación' }}</p>
                    <p class="text-lg mb-2"><strong>Descripción:</strong> {{ $event->description ?? 'Sin descripción' }}</p>
                    <p class="text-lg mb-2"><strong>Link del Evento:</strong> {{ $event->link }}</p>
                </div>

                <div class="mb-4 border border-neutral-200 dark:border-neutral-700 p-4 rounded-lg">
                    <h2 class="text-2xl font-semibold mb-2">Código QR del Evento</h2>
                        <div class="flex items-center justify-center">
                            {{-- {!! QrCode::size(200)->generate(route('events.attendance', $event->code)) !!} 
                            {!! QrCode::size(200)->generate($event->link) !!} --}}
                        </div>
                </div>
        </div>
    
            <livewire:card-stat title="Asistencias totales" :value="$asistenciasCount">
                <x-slot name="icon">
                    <flux:icon.list-checks class="size-8" />
                </x-slot>
            </livewire:card-stat>

        </div>

    </div>

</x-layouts.app>





{{-- <div class="mb-4 border border-neutral-200 dark:border-neutral-700 p-4 rounded-lg">
                    <h2 class="text-2xl font-semibold mb-2">Código QR del Evento</h2>
                    <div class="flex items-center justify-center">
                        {!! QrCode::size(200)->generate(route('events.attendance', $event->code)) !!}
                <p class="text-lg mb-2"><strong>Link del evento:</strong> {{ $event->link}}</p>
                </div> --}}