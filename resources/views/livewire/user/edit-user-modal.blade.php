<div>
    <flux:modal
        name="edit-user-modal"
        variant="flyout"
        class="w-full max-w-lg bg-zinc-50 dark:bg-zinc-900 [&::backdrop]:bg-black/40 [&::backdrop]:backdrop-blur-[2px]"
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
        <div class="space-y-6 bg-zinc-50 dark:bg-zinc-900 -m-6 p-6 min-h-full">
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
            <form wire:submit="save" class="space-y-5">

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
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Rol del usuario
                    </label>
                    <select wire:model.live="role"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecciona un rol</option>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- DEPENDENCIAS --}}
                @if($role === 'user')
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Dependencias
                        </label>
                        <div class="max-h-48 overflow-y-auto rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 p-3 space-y-2">
                            @foreach($dependencies as $value => $label)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700 rounded p-1.5 transition-colors">
                                    <input 
                                        type="checkbox" 
                                        wire:model="dependency_ids" 
                                        value="{{ $value }}"
                                        class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('dependency_ids')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                {{-- PASSWORD --}}
                <flux:input 
                    wire:model="password" 
                    :label="__('Nueva contraseña')" 
                    type="password" 
                    placeholder="Dejar vacío para no cambiar" 
                />
                <p class="text-xs text-gray-500 -mt-3">Solo completa si deseas cambiar la contraseña.</p>

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