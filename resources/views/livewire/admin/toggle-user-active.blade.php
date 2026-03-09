<div>
    {{-- Botón --}}
    <div class="flex justify-end mt-8">
        @if($user->is_active)
            <flux:modal.trigger name="toggle-user-{{ $user->id }}">
                <flux:button 
                    class="!bg-red-600 !hover:bg-red-700 !text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors cursor-pointer">
                    Desactivar usuario
                </flux:button>
            </flux:modal.trigger>
        @else
            <flux:modal.trigger name="toggle-user-{{ $user->id }}">
                <flux:button 
                    class="!bg-green-600 !hover:bg-green-700 !text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors cursor-pointer">
                    Activar usuario
                </flux:button>
            </flux:modal.trigger>
        @endif
    </div>

    {{-- Modal --}}
    <flux:modal name="toggle-user-{{ $user->id }}" class="min-w-[22rem] " x-on:click="$dispatch('modal-close', { name: 'toggle-user-{{ $user->id }}' })">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $user->is_active ? 'Desactivar' : 'Activar' }} usuario
                </flux:heading>
                <flux:text class="mt-2">
                    @if($user->is_active)
                        ¿Estás seguro de que deseas desactivar a <strong>{{ $user->name }}</strong>? No podrá iniciar sesión.
                    @else
                        ¿Deseas reactivar a <strong>{{ $user->name }}</strong>?
                    @endif
                </flux:text>
            </div>
            <div class="flex gap-2 justify-end">
                <flux:button 
                    variant="ghost"
                    class="cursor-pointer"
                    x-on:click="$dispatch('modal-close', { name: 'toggle-user-{{ $user->id }}' })">
                    Cancelar
                </flux:button>
                <flux:button 
                    wire:click="toggleActive"
                    class="{{ $user->is_active ? '!bg-red-600 hover:!bg-red-700' : '!bg-green-600 hover:!bg-green-700' }} !text-white cursor-pointer">
                    {{ $user->is_active ? 'Confirmar desactivación' : 'Confirmar activación' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>