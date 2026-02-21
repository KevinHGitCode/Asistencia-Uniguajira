<x-layouts.app :title="__('New')">
    <div>
        <h1 class="text-2xl font-bold mb-4"> Nuevo evento </h1>

        <div class="border border-zinc-500 rounded-lg p-6 dark:bg-zinc-900">
            <form id="event-form" action="{{ route('events.new.store') }}" method="POST" class="flex flex-col gap-6">
                @csrf

                <flux:input name="title" :label="__('Nombre del evento')" type="text" required autofocus
                    placeholder="Día del amor y la amistad" :value="old('title')" />

                <flux:input name="description" :label="__('Descripción del evento')" type="text"
                    placeholder="Evento especial..." :value="old('description')" />

                <flux:input name="location" :label="__('Ubicación del evento')" type="text"
                    placeholder="Auditorio principal, Uniguajira" :value="old('location')" />

                @php
                    $isAdmin = auth()->user()->role === 'admin';
                    $showDependencySelect = $isAdmin || $dependencies->count() > 1;
                @endphp

                {{-- CASO 1 y 2: Admin o usuario con varias dependencias → mostrar select --}}
                @if($showDependencySelect)
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Dependencia del evento
                        </label>
                        <select id="dependencySelect" name="dependency_id"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                            @if($isAdmin)
                                {{-- Admin: puede dejar sin dependencia --}}
                                <option value="">— Ninguna —</option>
                            @else
                                {{-- Usuario con varias: forzar a elegir --}}
                                <option value="">Selecciona una dependencia</option>
                            @endif

                            @foreach ($dependencies as $dependency)
                                <option value="{{ $dependency->id }}"
                                    {{ old('dependency_id') == $dependency->id ? 'selected' : '' }}>
                                    {{ $dependency->name }}
                                </option>
                            @endforeach
                        </select>

                        @if($isAdmin)
                            <p class="text-sm text-gray-500 mt-1">
                                Si no seleccionas una dependencia, el evento no estará asociado a ninguna.
                            </p>
                        @endif
                    </div>

                {{-- CASO 3: Usuario con una sola dependencia → campo oculto --}}
                @else
                    <input type="hidden" name="dependency_id" id="dependencyHidden"
                        value="{{ $selectedDependency }}">
                @endif

                {{-- ÁREA --}}
                {{-- 
                    Caso 1 y 2: disabled hasta que se elija dependencia (JS lo habilita)
                    Caso 3:     se carga desde el controlador, habilitado si hay áreas
                --}}
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Área <span class="text-gray-400">(opcional)</span>
                    </label>
                    <select id="areaSelect" name="area_id"
                        {{ ($showDependencySelect || $areas->isEmpty()) ? 'disabled' : '' }}
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">

                        <option value="">Selecciona un área (opcional)</option>

                        {{-- Caso 3: áreas precargadas desde el controlador --}}
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}"
                                {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-500 mt-1">
                        El área es opcional; solo se puede seleccionar cuando la dependencia tenga áreas.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:input name="date" :label="__('Fecha del evento')" type="date" required :value="old('date')" />
                    <flux:input name="start_time" :label="__('Hora de inicio')" type="time" :value="old('start_time')" />
                    <flux:input name="end_time" :label="__('Hora de finalización')" type="time" :value="old('end_time')" />
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

                <div class="flex justify-start gap-4 items-center">
                    <flux:button variant="primary" type="submit" class="px-3 py-6">
                        {{ __('Crear evento') }}
                    </flux:button>

                    @if (session()->has('success'))
                        <div class="bg-green-300 border border-green-400 text-green-700 px-4 py-3 rounded">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- 
        JS solo aplica a Casos 1 y 2 (cuando existe #dependencySelect).
        Caso 3 no necesita JS: las áreas vienen precargadas del controlador.
    --}}
    @if($showDependencySelect)
        @vite('resources/js/events/events-create.js')
    @endif

</x-layouts.app>