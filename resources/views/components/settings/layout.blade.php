@props(['showAvatarUpload' => false])

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">

        @php $settingsUser = auth()->user(); @endphp
        <div class="mb-6 flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">

            {{-- En /settings/profile: avatar interactivo con upload.
                 En otras páginas de settings: HTML estático (sin WithFileUploads) --}}
            @if($showAvatarUpload)
                <livewire:user.avatar
                    :user="$settingsUser"
                    size="h-14 w-14"
                    textSize="text-xl"
                    :showUpload="true" />
            @else
                <div class="flex-shrink-0"
                     x-data="{ avatarUrl: @js($settingsUser->avatar ? Storage::url($settingsUser->avatar) : null) }"
                     x-on:avatar-updated.window="avatarUrl = $event.detail.newAvatarUrl">
                    <template x-if="avatarUrl">
                        <img class="h-14 w-14 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600"
                             :src="avatarUrl"
                             alt="{{ $settingsUser->name }}">
                    </template>
                    <template x-if="!avatarUrl">
                        <div class="h-14 w-14 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center border-2 border-gray-200 dark:border-gray-600">
                            <span class="text-xl font-bold uppercase text-gray-800 dark:text-white">
                                {{ substr($settingsUser->name, 0, 1) }}
                            </span>
                        </div>
                    </template>
                </div>
            @endif

            <!-- Información del usuario -->
            <div class="flex-1 min-w-0 overflow-hidden">
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                    {{ $settingsUser->name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ $settingsUser->email }}
                </p>
            </div>
        </div>

        <flux:navlist>
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.language')" wire:navigate>{{ __('Language') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.about')" wire:navigate>{{ __('About') }}</flux:navlist.item>
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