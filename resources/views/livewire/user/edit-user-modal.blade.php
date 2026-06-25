<div>
    <flux:modal
        name="edit-user-modal"
        class="w-full max-w-2xl overflow-visible bg-zinc-50 dark:bg-zinc-900 [&>div]:overflow-visible [&::backdrop]:bg-black/40 [&::backdrop]:backdrop-blur-[2px]"
        x-data
        x-init="
        $nextTick(() => {
            const closeButton = $el.querySelector('[data-flux-modal-close]');
            if (closeButton) {
                closeButton.addEventListener('click', () => {
                    $dispatch('modal-close', { name: 'edit-user-modal' });
                });
            }
        });
    ">
        <div class="relative z-20 space-y-6 overflow-visible">
            {{-- Header --}}
            <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="size-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L12 15l-4 1 1-4 8.586-8.586z"/>
                    </svg>
                    <flux:heading size="lg">Editar usuario</flux:heading>
                </div>
                <flux:text class="mt-1 text-zinc-500">Modifica los datos del usuario.</flux:text>
            </div>

            {{-- Formulario --}}
            <form wire:submit="save" class="relative z-20 space-y-5 overflow-visible">

                <div class="relative z-20 grid grid-cols-1 gap-x-4 gap-y-5 overflow-visible sm:grid-cols-2">

                    <flux:input
                        wire:model="name"
                        :label="__('Nombre completo')"
                        type="text"
                        required
                        placeholder="Nombre completo"
                    />

                    <flux:input
                        wire:model="email"
                        :label="__('Correo electrónico')"
                        type="email"
                        required
                        placeholder="ejemplo@correo.com"
                    />

                    {{-- ROL --}}
                    <div class="relative z-40 flex flex-col gap-1">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Rol del usuario
                        </label>
                        <x-ui.searchable-select
                            wire:key="edit-user-role-{{ $userId ?? 'none' }}-{{ $role ?: 'none' }}"
                            wire:model.live="role"
                            :options="$roles"
                            placeholder="Selecciona un rol"
                            empty-label="Selecciona un rol"
                            search-placeholder="Buscar rol…" />
                        @error('role')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- SEDE --}}
                    @if($role !== 'superadmin')
                        <div class="relative z-30 flex flex-col gap-1">
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Sede
                            </label>
                            <x-ui.searchable-select
                                wire:model.live="campus_id"
                                :options="$campuses"
                                placeholder="Selecciona una sede"
                                empty-label="Selecciona una sede"
                                search-placeholder="Buscar sede..." />
                            @error('campus_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    {{-- DEPENDENCIAS --}}
                    @if($role === 'user')
                        <div class="relative z-20 flex flex-col gap-2 sm:col-span-2">
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                Dependencias
                            </label>
                            <x-ui.multi-searchable-select
                                wire:key="edit-user-dependencies-{{ $campus_id ?? 'none' }}"
                                wire:model="dependency_ids"
                                :options="$this->filteredDependencies"
                                placeholder="Agregar dependencias…"
                                search-placeholder="Buscar dependencia…" />
                            @error('dependency_ids')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    {{-- ESTADO (activar/desactivar según jerarquía; se aplica al guardar) --}}
                    @if($canToggleActive)
                        <div class="relative z-10 flex flex-col gap-1">
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Estado</label>
                            <x-ui.searchable-select
                                wire:key="edit-user-status-{{ $userId ?? 'none' }}"
                                wire:model="activeState"
                                :options="[['id' => '1', 'name' => 'Activo'], ['id' => '0', 'name' => 'Inactivo']]"
                                :allow-empty="false"
                                placeholder="Selecciona un estado" />
                            <p class="text-xs text-gray-500">Se aplicará al guardar los cambios.</p>
                        </div>
                    @endif

                    {{-- PASSWORD --}}
                    <div class="sm:col-span-2">
                        <flux:input
                            wire:model="password"
                            :label="__('Nueva contraseña')"
                            type="password"
                            placeholder="Dejar vacío para no cambiar"
                        />
                        <p class="text-xs text-gray-500 mt-1">Solo completa si deseas cambiar la contraseña.</p>
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
                        <span wire:loading.remove wire:target="save">Guardar cambios</span>
                        <span wire:loading wire:target="save">Guardando...</span>
                    </flux:button>
                    <flux:button
                        variant="ghost"
                        class="cursor-pointer"
                        x-on:click="$dispatch('modal-close', { name: 'edit-user-modal' })">
                        Cancelar
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
