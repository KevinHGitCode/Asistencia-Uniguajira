<x-layouts.app :title="__('New')">
    <div>
        <h1 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white"> Nuevo evento </h1>

        <div class="border border-white rounded-lg p-6 bg-gray-800">
            <form action="{{ route('events.new.store') }}" method="POST" class="flex flex-col gap-6">
                @csrf

                <!-- Cambiar 'eventName' por 'title' -->
                <flux:input name="title" :label="__('Nombre del evento')" type="text" required autofocus
                    placeholder="Día del amor y la amistad" />

                <flux:input name="description" :label="__('Descripción del evento')" type="text" required
                    placeholder="Evento especial para celebrar el amor y la amistad" />

                    {{-- //TODO: que tenga datos por defecto con la fecha actual y la hora actual pero cerrada (terminando en 00)--}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:input name="date" :label="__('Fecha del evento')" type="date" required />

                    <!-- Cambiar 'startTime' por 'start_time' -->
                    <flux:input name="start_time" :label="__('Hora de inicio')" type="time" required />

                    <!-- Cambiar 'endTime' por 'end_time' -->
                    <flux:input name="end_time" :label="__('Hora de finalización')" type="time" required />
                </div>

                <!-- Mostrar errores de validación -->
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex justify-start">
                    <flux:button variant="primary" type="submit" class="px-3 py-6">
                        {{ __('Crear Evento') }}
                    </flux:button>

                    
                        @if (session()->has('success'))
                            <div class=" gap-6 ml-4 bg-green-300 border border-green-400 text-green-700 px-4 py-3 rounded">
                            <p style="color: green;">{{ session('success') }}</p>
                            </div>
                        @endif
                    
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
