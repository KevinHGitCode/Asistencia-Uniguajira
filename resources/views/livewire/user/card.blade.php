<div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 ">

    <div class="flex items-center gap-4">
        <div>
            <flux:avatar src="https://unavatar.io/x/calebporzio" />
        </div>

        <div class="mb-4">
            <flux:heading class="flex items-center gap-2">
                {{ $title }}
            </flux:heading>
        </div>

        <div class="ml-auto">
            {{-- botón detalles a la derecha --}}
            <a href="{{ route('users.information', ['user' => $user->id]) }}">
                <flux:button square>...</flux:button>
            </a>
        </div>
    </div>

</div>