<div>
    <flux:modal name="create-user-modal" variant="flyout" class="w-full max-w-lg" x-data x-init="
        $nextTick(() => {
            const closeButton = $el.querySelector('[data-flux-modal-close]');
            if (closeButton) {
                closeButton.addEventListener('click', () => {
                    $dispatch('modal-close', { name: 'create-user-modal' });
                });
            }
        });
    ">
        <div class="space-y-6">
            {{-- Header --}}
            <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="size-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <flux:heading size="lg">Nuevo usuario</flux:heading>
                </div>
                <flux:text class="mt-1 text-zinc-500">Completa los datos para registrar un nuevo usuario.</flux:text>
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

                {{-- DEPENDENCIAS (solo visible si el rol es user) --}}
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
                        <p class="text-xs text-gray-500">Selecciona una o más dependencias para este usuario.</p>
                    </div>
                @endif

                {{-- PASSWORD --}}
                <flux:input 
                    wire:model="password" 
                    :label="__('Contraseña')" 
                    type="password" 
                    required 
                    placeholder="Contraseña segura" 
                />

                {{-- ERRORES GENERALES --}}
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
                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">Crear usuario</span>
                        <span wire:loading wire:target="save">Creando...</span>
                    </flux:button>
                    <flux:button 
                        variant="ghost" 
                        x-on:click="$dispatch('modal-close', { name: 'create-user-modal' })">
                        Cancelar
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>