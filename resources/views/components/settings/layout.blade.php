<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">

          <!-- Avatar del usuario con diseño mejorado -->
        <div class="mb-6 flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <!-- Avatar con ancho fijo -->
            <div class="flex-shrink-0">
                @livewire('user.avatar', [
                    'user' => auth()->user(),
                    'size' => 'h-14 w-14',
                    'textSize' => 'text-xl',
                    'showUpload' => true
                ], key('avatar-'.auth()->user()->id))
            </div>
            
            <!-- Información del usuario -->
            <div class="flex-1 min-w-0 overflow-hidden">
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                    {{ auth()->user()->name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ auth()->user()->email }}
                </p>
            </div>
        </div>

        <flux:navlist>
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.language')" wire:navigate>{{ __('Language') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>