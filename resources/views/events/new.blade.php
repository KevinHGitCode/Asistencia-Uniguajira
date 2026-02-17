<x-layouts.app :title="__('New')">
    <div>
        <h1 class="text-2xl font-bold mb-4"> Nuevo evento </h1>

        <div class="border border-zinc-500 rounded-lg p-6 dark:bg-zinc-900">
            <form id="event-form" action="{{ route('events.new.store') }}" method="POST" class="flex flex-col gap-6">
                @csrf

                <flux:input name="title" :label="__('Nombre del evento')" type="text" required autofocus
                    placeholder="Día del amor y la amistad" :value="old('title')" class="custom-input" />

                <flux:input name="description" :label="__('Descripción del evento')" type="text"
                    placeholder="Evento especial..." :value="old('description')" />

                <flux:input name="location" :label="__('Ubicación del evento')" type="text"
                    placeholder="Auditorio principal, Uniguajira" :value="old('location')" />

                {{-- DEPENDENCY: mostrar select solo si:
                     - admin (puede elegir "Ninguna") o
                     - usuario normal con >1 dependencias --}}
                @php
                    $showDependencySelect = auth()->user()->role === 'admin' || (!isset($selectedDependency) && $dependencies->count() > 1);
                @endphp

                {{-- DEPENDENCY --}}
                @if($showDependencySelect)
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Dependencia del evento
                        </label>
                        <select id="dependencySelect" name="dependency_id"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @if(auth()->user()->role === 'admin')
                                <option value="">Ninguna</option>
                            @endif
                            @foreach ($dependencies as $dependency)
                                <option value="{{ $dependency->id }}"
                                    {{ old('dependency_id') == $dependency->id ? 'selected' : '' }}>
                                    {{ $dependency->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500">Si no seleccionas una dependencia y eres administrador, el evento no estará asociado a ninguna.</p>
                    </div>
                    @else
                        <input type="hidden" name="dependency_id" id="dependencyHidden" value="{{ $selectedDependency }}">
                    @endif

                    {{-- AREA --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Área (opcional)
                        </label>
                        <select id="areaSelect" name="area_id" disabled
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">Selecciona un área (opcional)</option>
                        </select>
                        <p class="text-sm text-gray-500">El área es opcional; solo se puede seleccionar cuando la dependencia tenga áreas.</p>
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

                <div class="flex justify-start">
                    <flux:button variant="primary" type="submit" class="px-3 py-6">
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

    {{-- SCRIPT: controla llenado de areas y habilitado --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const dependencySelect = document.getElementById('dependencySelect');
            const dependencyHidden = document.getElementById('dependencyHidden');
            const areaSelect = document.getElementById('areaSelect');

            function clearDisableArea() {
                areaSelect.innerHTML = '<option value="">Selecciona un área (opcional)</option>';
                areaSelect.disabled = true;
            }

            async function loadAreasFor(dependencyId) {
                if (!dependencyId || dependencyId === '' || dependencyId === '0') {
                    clearDisableArea();
                    return;
                }

                try {
                    const response = await fetch(`/dependencies/${dependencyId}/areas`);
                    if (!response.ok) { clearDisableArea(); return; }

                    const areas = await response.json();
                    if (!areas.length) { clearDisableArea(); return; }

                    let options = '<option value="">Selecciona un área (opcional)</option>';
                    areas.forEach(area => {
                        options += `<option value="${area.id}">${area.name}</option>`;
                    });

                    areaSelect.innerHTML = options;
                    areaSelect.disabled = false;

                } catch (e) {
                    clearDisableArea();
                }
            }

            // Carga inicial
            const initialDependency = dependencyHidden?.value || dependencySelect?.value;
            if (initialDependency) {
                loadAreasFor(initialDependency);
            }

            // Cambio dinámico
            dependencySelect?.addEventListener('change', e => loadAreasFor(e.target.value));
        });
    </script>
</x-layouts.app>
