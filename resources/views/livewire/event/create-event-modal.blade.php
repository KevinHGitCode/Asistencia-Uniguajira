<div>
    <flux:modal name="create-event-modal" variant="flyout" class="w-full max-w-lg bg-zinc-50 dark:bg-zinc-900 [&::backdrop]:bg-black/40 [&::backdrop]:backdrop-blur-[2px]" x-data x-init="
        $nextTick(() => {
            const closeButton = $el.querySelector('[data-flux-modal-close]');
            if (closeButton) {
                closeButton.addEventListener('click', () => {
                    $dispatch('modal-close', { name: 'create-event-modal' });
                });
            }
        });
    ">
        <div class="space-y-6">
            {{-- Header --}}
            <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
                <div class="flex items-center gap-2 mb-1">
                    <flux:icon.calendar-check class="size-6 text-[#e2a542]" />
                    <flux:heading size="lg">Nuevo evento</flux:heading>
                </div>
                <flux:text class="mt-1 text-zinc-500">Completa los datos para crear un nuevo evento.</flux:text>
            </div>

            {{-- Formulario --}}
            <form wire:submit="save" class="space-y-5">

                <flux:input 
                    wire:model="title" 
                    :label="__('Nombre del evento')" 
                    type="text" 
                    required 
                    placeholder="Día del amor y la amistad" 
                />

                <flux:input 
                    wire:model="description" 
                    :label="__('Descripción del evento')" 
                    type="text" 
                    placeholder="Evento especial..." 
                />

                <flux:input 
                    wire:model="location" 
                    :label="__('Ubicación del evento')" 
                    type="text" 
                    placeholder="Auditorio principal, Uniguajira" 
                />

                {{-- DEPENDENCIA --}}
                @if($showDependencySelect)
                    {{-- CASO 1 y 2: Admin o usuario con varias dependencias --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Dependencia del evento
                        </label>
                        <select wire:model.live="dependency_id"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ $isAdmin ? '— Ninguna —' : 'Selecciona una dependencia' }}</option>
                            @foreach($dependencies as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @if($isAdmin)
                            <p class="text-xs text-gray-500">Si no seleccionas una dependencia, el evento no estará asociado a ninguna.</p>
                        @endif
                    </div>
                @else
                    {{-- CASO 3: Usuario con una sola dependencia → oculto, ya precargado --}}
                    <input type="hidden" wire:model="dependency_id">
                @endif

                {{-- ÁREA (deshabilitado temporalmente — no se usa actualmente)
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Área (opcional)
                    </label>
                    <select wire:model="area_id"
                        @if(collect($areas)->isEmpty()) disabled @endif
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <option value="">Selecciona un área (opcional)</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500">El área solo se puede seleccionar cuando la dependencia tenga áreas.</p>
                </div>
                --}}

                {{-- FECHA Y HORAS --}}
                <div class="grid grid-cols-1 gap-4">
                    <flux:input 
                        wire:model="date" 
                        :label="__('Fecha del evento')" 
                        type="date" 
                        required 
                        :min="now()->format('Y-m-d')"
                    />
                    <div class="grid grid-cols-2 gap-4">
                        <flux:input 
                            wire:model="start_time" 
                            :label="__('Hora de inicio')" 
                            type="time" 
                        />
                        <flux:input 
                            wire:model="end_time" 
                            :label="__('Hora de finalización')" 
                            type="time" 
                        />
                    </div>
                </div>

                {{-- ERRORES --}}
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- BOTONES --}}
                <div class="flex items-center gap-3 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:button variant="primary" class="cursor-pointer" type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">Crear evento</span>
                        <span wire:loading wire:target="save">Creando...</span>
                    </flux:button>
                    <flux:button 
                        variant="ghost" 
                        class="cursor-pointer"
                        x-on:click="$dispatch('modal-close', { name: 'create-event-modal' })">
                        Cancelar
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>