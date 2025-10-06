<x-layouts.app-nosidebar :title="__('Acceso al evento')">
    <div class="max-w-md mx-auto mt-10 p-6 border rounded-lg dark:bg-black text-center">
        <h2 class="text-xl font-bold mb-4">Acceder al evento</h2>
        <p class="mb-4 text-gray-600 dark:text-gray-300">
            Evento: <span class="font-semibold">{{ $event->title }}</span>
        </p>

        <form method="POST" action="#">
            @csrf
            <flux:input name="identification" :label="__('Número de identificación')" type="text"
                required placeholder="Ingresa tu número de identificación" />

            <div class="mt-4">
                <flux:button variant="primary" type="submit">
                    {{ __('Acceder') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts.app-nosidebar>
