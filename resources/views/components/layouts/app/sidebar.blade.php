<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse hover:scale-103 transition-transform" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">

                <flux:navlist.item icon="home" :href="route('dashboard')" class="hover:scale-103 transition-transform" :current="request()->routeIs('dashboard')"
                    wire:navigate>{{ __('Home') }}</flux:navlist.item>

                <flux:navlist.item icon="plus" :href="route('events.new')" class="hover:scale-103 transition-transform" :current="request()->routeIs('events.new')"
                    wire:navigate>{{ __('New') }}</flux:navlist.item>

                <flux:navlist.item icon="numbered-list" :href="route('events.list')" class="hover:scale-103 transition-transform" :current="request()->routeIs('events.list')"
                    wire:navigate>{{ __('Event list') }}</flux:navlist.item>

                <flux:navlist.item icon="chart-bar" :href="route('statistics')" class="hover:scale-103 transition-transform" :current="request()->routeIs('statistics')"
                    wire:navigate>{{ __('Statistics') }}</flux:navlist.item>

                <flux:navlist.item icon="chart-bar" :href="route('charts.types')" class="hover:scale-103 transition-transform" :current="request()->routeIs('charts.types')"
                    wire:navigate>{{ __('Tipos de graficos') }}</flux:navlist.item>

                @if(auth()->user()->role === 'admin')
                    <flux:navlist.item icon="users" :href="route('users.index')" class="hover:scale-103 transition-transform" :current="request()->routeIs('users.index')"
                        wire:navigate>{{ __('Users') }}</flux:navlist.item>
                @endif
                    
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
           <flux:profile name="{{ auth()->user()->name }}"  avatar="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : null }}"
             icon:trailing="chevrons-up-down"
            class="flex items-center space-x-2"
            avatar-class="h-8 w-8 rounded-full object-cover"/>
            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                 @if (auth()->user()->avatar)
                                       <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                        alt="{{ auth()->user()->name }}"
                                        class="h-full w-full object-cover rounded-lg">
                                    @else
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {{ auth()->user()->initials() }}
                                        </span>
                                     @endif
                            </span>


                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                        {{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
           
        <flux:profile
            avatar="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : null }}"
            icon-trailing="chevron-down"
            class="flex items-center space-x-2"
            avatar-class="h-8 w-8 rounded-full object-cover"/>
            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                 @if (auth()->user()->avatar)
                                       <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                        alt="{{ auth()->user()->name }}"
                                        class="h-full w-full object-cover rounded-lg">
                                    @else
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {{ auth()->user()->initials() }}
                                        </span>
                                     @endif
                            </span>


                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                        {{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
