<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        {{-- Debounce wrapper: prevents rapid multi-clicks on wire:navigate links.
             Default cooldown: 500ms. Statistics links use data-debounce="1000".
             Any link can override via data-debounce="<ms>". --}}
        <div x-data="{
            _t: {},
            _check(e) {
                const a = e.target.closest('[wire\\:navigate]');
                if (!a) return;
                const href = a.getAttribute('href') || a.textContent.trim();
                const delay = Number(a.dataset.debounce ?? 500);
                const now = Date.now();
                if (this._t[href] && now - this._t[href] < delay) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                } else {
                    this._t[href] = now;
                }
            }
        }" @click.capture="_check($event)">

        <a href="{{ route('dashboard') }}" class="aura-sidebar-link group flex w-full items-center justify-center" wire:navigate>
            <x-app-logo />
        </a>

        <style>
            .aura-sidebar-link .aura-logo-sidebar {
                transition: transform 300ms cubic-bezier(0.34, 1.56, 0.64, 1),
                            filter 300ms ease-out;
                transform-origin: center;
            }

            .aura-sidebar-link:hover .aura-logo-sidebar {
                transform: scale(1.18) rotate(-2deg);
                filter: drop-shadow(0 6px 14px rgba(98, 169, 182, 0.55));
            }

            .aura-sidebar-link:active .aura-logo-sidebar {
                transform: scale(1.08) rotate(-1deg);
                transition-duration: 120ms;
            }
        </style>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">

                <flux:navlist.item icon="home" :href="route('dashboard')" class="hover:scale-103 transition-transform" :current="request()->routeIs('dashboard')"
                    wire:navigate>{{ __('Home') }}</flux:navlist.item>

                <flux:navlist.item icon="plus" :href="route('events.new')" class="hover:scale-103 transition-transform" :current="request()->routeIs('events.new')"
                    wire:navigate>{{ __('New') }}</flux:navlist.item>

                <flux:navlist.item icon="calendar-check" :href="route('events.list')" class="hover:scale-103 transition-transform" :current="request()->routeIs('events.list')"
                    wire:navigate>{{ __('Your Events') }}</flux:navlist.item>

                @if(auth()->user()->role === 'admin')
                    <flux:navlist.item icon="numbered-list" :href="route('admin.events.index')" class="hover:scale-103 transition-transform" :current="request()->routeIs('admin.events.index')"
                        wire:navigate>{{ __('All Events') }}</flux:navlist.item>
                @endif

                {{-- Estadísticas: enlace al overview + lista colapsable de sub-módulos --}}
                <style>[x-cloak]{display:none!important}</style>
                <div x-data="{
                    open: false,
                    ready: false,
                    init() {
                        if (@js(request()->routeIs('statistics*'))) {
                            this.open = true;
                        } else {
                            this.open = localStorage.getItem('sidebar-stats-open') === 'true';
                        }
                        this.$nextTick(() => { this.ready = true; });
                    }
                }" x-effect="localStorage.setItem('sidebar-stats-open', open)" class="my-px">

                    {{-- Fila: enlace overview + botón toggle --}}
                    <div class="flex items-center">
                        <a href="{{ route('statistics') }}" wire:navigate data-debounce="1000"
                           @class([
                               'h-10 lg:h-8 flex flex-1 min-w-0 items-center gap-3 rounded-lg pl-3 pr-1 text-sm font-medium leading-none border transition-colors duration-150 hover:scale-103',
                               'text-zinc-500 dark:text-white/80 border-transparent hover:text-zinc-800 hover:bg-zinc-800/5 dark:hover:text-white dark:hover:bg-white/[7%]' => !request()->routeIs('statistics'),
                               'text-[--color-accent-content] bg-white dark:bg-white/[7%] border-zinc-200 dark:border-transparent' => request()->routeIs('statistics'),
                           ])>
                            <flux:icon.chart-bar class="size-4! shrink-0" />
                            <span class="truncate">{{ __('Statistics') }}</span>
                        </a>

                        {{-- Botón chevron --}}
                        <button
                            type="button"
                            @click="open = !open"
                            class="h-8 w-7 flex shrink-0 items-center justify-center rounded-lg text-zinc-500 dark:text-white/80 hover:text-zinc-800 hover:bg-zinc-800/5 dark:hover:text-white dark:hover:bg-white/[7%] transition-colors"
                            :aria-expanded="open.toString()"
                        >
                            <span class="block"
                                  :class="[open ? 'rotate-90' : '', ready ? 'transition-transform duration-200' : '']">
                                <flux:icon.chevron-right class="size-3.5!" />
                            </span>
                        </button>
                    </div>

                    {{-- Sub-ítems --}}
                    <div
                        x-cloak
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="relative mt-0.5 flex flex-col gap-[2px] pl-6"
                    >
                        {{-- Línea vertical decorativa --}}
                        <div class="absolute inset-y-1 left-[11px] w-px rounded-full bg-zinc-200 dark:bg-white/20"></div>

                        <flux:navlist.item :href="route('statistics.asistencias')" :current="request()->routeIs('statistics.asistencias')" wire:navigate data-debounce="1000">
                            {{ __('Por Asistencias') }}
                        </flux:navlist.item>

                        <flux:navlist.item :href="route('statistics.participantes')" :current="request()->routeIs('statistics.participantes')" wire:navigate data-debounce="1000">
                            {{ __('Por Participantes') }}
                        </flux:navlist.item>

                        <flux:navlist.item :href="route('statistics.compara-eventos')" :current="request()->routeIs('statistics.compara-eventos')" wire:navigate data-debounce="1000">
                            {{ __('Compara Eventos') }}
                        </flux:navlist.item>
                        @if(auth()->user()->role === 'admin')
                            <flux:navlist.item :href="route('statistics.usuarios')" :current="request()->routeIs('statistics.usuarios')" wire:navigate data-debounce="1000">
                                {{ __('Por Usuarios') }}
                            </flux:navlist.item>
                        @endif
                    </div>
                </div>

                {{-- Tipos de grafico Echrats --}}
                {{-- <flux:navlist.item icon="chart-bar" :href="route('charts.types')" class="hover:scale-103 transition-transform" :current="request()->routeIs('charts.types')"
                    wire:navigate>{{ __('Chart Types') }}</flux:navlist.item> --}}

                @if(auth()->user()->role === 'admin')
                    <flux:navlist.item icon="users" :href="route('users.index')" class="hover:scale-103 transition-transform" :current="request()->routeIs('users.index')"
                        wire:navigate>{{ __('Users') }}</flux:navlist.item>
                @endif

                @if(auth()->user()->role === 'admin')
                    {{-- AdministraciÃ³n: enlace al overview + lista colapsable de sub-mÃ³dulos --}}
                    <div x-data="{
                        open: false,
                        ready: false,
                        init() {
                            if (@js(request()->routeIs('administracion.*', 'dependencies.*', 'areas.*', 'programs.*', 'formats.*', 'participant-types.*', 'affiliations.*', 'participants-import.*'))) {
                                this.open = true;
                            } else {
                                this.open = localStorage.getItem('sidebar-admin-open') === 'true';
                            }
                            this.$nextTick(() => { this.ready = true; });
                        }
                    }" x-effect="localStorage.setItem('sidebar-admin-open', open)" class="my-px">

                        {{-- Fila: enlace overview + botÃ³n toggle --}}
                        <div class="flex items-center">
                            <a href="{{ route('administracion.index') }}" wire:navigate
                               @class([
                                   'h-10 lg:h-8 flex flex-1 min-w-0 items-center gap-3 rounded-lg pl-3 pr-1 text-sm font-medium leading-none border transition-colors duration-150 hover:scale-103',
                                   'text-zinc-500 dark:text-white/80 border-transparent hover:text-zinc-800 hover:bg-zinc-800/5 dark:hover:text-white dark:hover:bg-white/[7%]' => !request()->routeIs('administracion.index'),
                                   'text-[--color-accent-content] bg-white dark:bg-white/[7%] border-zinc-200 dark:border-transparent' => request()->routeIs('administracion.index'),
                               ])>
                                <flux:icon.cog class="size-4! shrink-0" />
                                <span class="truncate">{{ __('Administration') }}</span>
                            </a>

                            {{-- BotÃ³n chevron --}}
                            <button
                                type="button"
                                @click="open = !open"
                                class="h-8 w-7 flex shrink-0 items-center justify-center rounded-lg text-zinc-500 dark:text-white/80 hover:text-zinc-800 hover:bg-zinc-800/5 dark:hover:text-white dark:hover:bg-white/[7%] transition-colors"
                                :aria-expanded="open.toString()"
                            >
                                <span class="block"
                                      :class="[open ? 'rotate-90' : '', ready ? 'transition-transform duration-200' : '']">
                                    <flux:icon.chevron-right class="size-3.5!" />
                                </span>
                            </button>
                        </div>

                        {{-- Sub-Ã­tems --}}
                        <div
                            x-cloak
                            x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="relative mt-0.5 flex flex-col gap-[2px] pl-6"
                        >
                            {{-- LÃ­nea vertical decorativa --}}
                            <div class="absolute inset-y-1 left-[11px] w-px rounded-full bg-zinc-200 dark:bg-white/20"></div>

                            <flux:navlist.item :href="route('dependencies.index')" :current="request()->routeIs('dependencies.*')" wire:navigate>
                                {{ __('Dependencias') }}
                            </flux:navlist.item>

                            {{-- <flux:navlist.item :href="route('areas.index')" :current="request()->routeIs('areas.*')" wire:navigate>
                                {{ __('Áreas') }}
                            </flux:navlist.item> --}}

                            <flux:navlist.item :href="route('programs.index')" :current="request()->routeIs('programs.*')" wire:navigate>
                                {{ __('Programas') }}
                            </flux:navlist.item>

                            <flux:navlist.item :href="route('formats.index')" :current="request()->routeIs('formats.*')" wire:navigate>
                                {{ __('Formatos') }}
                            </flux:navlist.item>

                            <flux:navlist.item :href="route('participant-types.index')" :current="request()->routeIs('participant-types.*')" wire:navigate>
                                {{ __('Estamentos') }}
                            </flux:navlist.item>

                            <flux:navlist.item :href="route('affiliations.index')" :current="request()->routeIs('affiliations.*')" wire:navigate>
                                {{ __('Afiliaciones') }}
                            </flux:navlist.item>

                            <flux:navlist.item :href="route('participants-import.index')" :current="request()->routeIs('participants-import.*')" wire:navigate>
                                {{ __('Participantes') }}
                            </flux:navlist.item>
                        </div>
                    </div>
                @endif

            </flux:navlist.group>
        </flux:navlist>

        </div>{{-- end debounce wrapper --}}

        <flux:spacer />

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
           <flux:profile name="{{ auth()->user()->name }}"
             avatar="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : null }}"
             icon:trailing="chevrons-up-down"
             circle />
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
            circle />
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
