@props(['event'])

<flux:modal name="delete-event-modal" class="max-w-md">
    <div class="space-y-6">
        <div class="text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <flux:heading size="lg">¿Eliminar evento?</flux:heading>
            <flux:text class="mt-2 text-zinc-500">
                Estás a punto de eliminar <span class="font-bold">{{ $event->title }}</span>. 
                Esta acción no se puede deshacer y se perderán todos los datos asociados.
            </flux:text>
        </div>

        <div class="flex items-center justify-center gap-3">
            <flux:button 
                variant="ghost" 
                x-on:click="$dispatch('modal-close', { name: 'delete-event-modal' })">
                Cancelar
            </flux:button>

            <form action="{{ route('events.destroy', $event->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <flux:button variant="danger" type="submit">
                    Sí, eliminar evento
                </flux:button>
            </form>
        </div>
    </div>
</flux:modal>