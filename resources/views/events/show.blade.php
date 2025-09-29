<x-layouts.app :title="__('Evento')">
    <div class="p-4">
        <h1 class="text-3xl font-bold mb-4">Detalles del Evento</h1>
        <p class="text-lg mb-2"><strong>Título:</strong> {{ $event->title }}</p>
        <p class="text-lg mb-2"><strong>Fecha:</strong> {{ $event->date }}</p>
        <p class="text-lg mb-2"><strong>Hora de Inicio:</strong> {{ $event->start_time }}</p>
        <p class="text-lg mb-2"><strong>Hora de Fin:</strong> {{ $event->end_time }}</p>
        <p class="text-lg mb-2"><strong>Ubicación:</strong> {{ $event->location ?? 'Sin ubicación' }}</p>
        <p class="text-lg mb-2"><strong>Descripción:</strong> {{ $event->description ?? 'Sin descripción' }}</p>
    </div>
    <a href="{{ route('events.list') }}" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Volver a la lista de eventos</a>
</x-layouts.app
