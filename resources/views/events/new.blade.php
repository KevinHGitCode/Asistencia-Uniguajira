<x-layouts.app :title="__('New')">
    <div>
        <h1 class="text-2xl font-bold mb-4"> Nuevo evento </h1>

        <div class="border border-zinc-500 rounded-lg p-6 dark:bg-zinc-900">
            <form action="{{ route('events.new.store') }}" method="POST" class="flex flex-col gap-6">
                @csrf

                <flux:input name="title" :label="__('Nombre del evento')" type="text" required autofocus
                    placeholder="Día del amor y la amistad" :value="old('title')" class="custom-input" />


                <flux:input name="description" :label="__('Descripción del evento')" type="text" required
                    placeholder="Evento especial para celebrar el amor y la amistad" :value="old('description')" />


                <flux:input name="location" :label="__('Ubicación del evento')" type="text" required
                    placeholder="Auditorio principal, Uniguajira" :value="old('location')" />

                @if (auth()->user()->role === 'admin')
                    <div>
                        <flux:select name="dependency_id" :label="__('Dependencia del evento')"
                            placeholder="Selecciona una dependencia">
                            <option value="">Ninguna</option>
                            @foreach ($dependencies as $dependency)
                                <option value="{{ $dependency->id }}"
                                    {{ old('dependency_id') == $dependency->id ? 'selected' : '' }}>
                                    {{ $dependency->name }}
                                </option>
                            @endforeach
                        </flux:select>
                        <p class="text-sm text-gray-500 mt-1">Si no seleccionas una dependencia, el evento no estará
                            asociado a ninguna.</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <flux:input name="date" :label="__('Fecha del evento')" type="date" required
                        :value="old('date')" />


                    <flux:input name="start_time" :label="__('Hora de inicio')" type="time" required
                        :value="old('start_time')" />


                    <flux:input name="end_time" :label="__('Hora de finalización')" type="time" required
                        :value="old('end_time')" />

                </div>

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
                    <flux:button variant="primary" type="submit"
                        class="px-3 py-6 dark:hover:bg-gray-300 hover:scale-103 transition-colors transition-transform">
                        {{ __('Create event') }}
                    </flux:button>

                    @if (session()->has('success'))
                        <div class=" gap-6 ml-4 bg-green-300 border border-green-400 text-green-700 px-4 py-3 rounded">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
