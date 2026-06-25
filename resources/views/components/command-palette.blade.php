{{--
    Paleta de comandos para administradores (ADR-0007).

    Se monta una sola vez en el layout (app/sidebar.blade.php), solo para usuarios
    con acceso de administrador. Atajo Cmd/Ctrl+K; también se abre con el evento
    `open-command-palette` (botón del sidebar). La lógica vive en
    resources/js/components/command-palette.js.
--}}
@php
    $user = auth()->user();
@endphp

@if($user && $user->hasAdminAccess())
    @php
        $isSuperadmin = $user->isSuperadmin();

        // Registro de comandos. Mantener al alza con los módulos del sidebar.
        // 'search' = sinónimos/keywords; se le antepone la etiqueta más abajo.
        $commands = array_values(array_filter([
            ['label' => 'Inicio', 'group' => 'Navegación', 'url' => route('dashboard'), 'search' => 'dashboard home calendario'],
            ['label' => 'Nuevo evento', 'group' => 'Acciones', 'url' => route('events.new'), 'search' => 'crear registrar'],
            ['label' => 'Tus eventos', 'group' => 'Navegación', 'url' => route('events.list'), 'search' => 'mis eventos lista'],
            ['label' => 'Todos los eventos', 'group' => 'Navegación', 'url' => route('admin.events.index'), 'search' => 'admin'],

            ['label' => 'Estadísticas', 'group' => 'Estadísticas', 'url' => route('statistics'), 'search' => 'graficos metricas resumen'],
            ['label' => 'Estadísticas · Asistencias', 'group' => 'Estadísticas', 'url' => route('statistics.asistencias'), 'search' => 'graficos'],
            ['label' => 'Estadísticas · Participantes', 'group' => 'Estadísticas', 'url' => route('statistics.participantes'), 'search' => 'graficos'],
            ['label' => 'Estadísticas · Compara eventos', 'group' => 'Estadísticas', 'url' => route('statistics.compara-eventos'), 'search' => 'comparar'],
            ['label' => 'Estadísticas · Usuarios', 'group' => 'Estadísticas', 'url' => route('statistics.usuarios'), 'search' => 'graficos'],

            ['label' => 'Usuarios', 'group' => 'Administración', 'url' => route('users.index'), 'search' => 'cuentas gestionar'],
            ['label' => 'Administración', 'group' => 'Administración', 'url' => route('administracion.index'), 'search' => 'configuracion ajustes'],
            $isSuperadmin
                ? ['label' => 'Sedes', 'group' => 'Administración', 'url' => route('campuses.index'), 'search' => 'campus']
                : null,
            ['label' => 'Dependencias', 'group' => 'Administración', 'url' => route('dependencies.index'), 'search' => ''],
            ['label' => 'Programas', 'group' => 'Administración', 'url' => route('programs.index'), 'search' => 'academicos'],
            $isSuperadmin
                ? ['label' => 'Formatos', 'group' => 'Administración', 'url' => route('formats.index'), 'search' => 'pdf plantillas']
                : null,
            ['label' => 'Estamentos', 'group' => 'Administración', 'url' => route('participant-types.index'), 'search' => 'tipos de participante'],
            ['label' => 'Afiliaciones', 'group' => 'Administración', 'url' => route('affiliations.index'), 'search' => ''],
            ['label' => 'Organizaciones', 'group' => 'Administración', 'url' => route('organizations.index'), 'search' => ''],
            ['label' => 'Participantes', 'group' => 'Administración', 'url' => route('participants-import.index'), 'search' => 'importar cargar'],
            $isSuperadmin
                ? ['label' => 'Registros de actividad', 'group' => 'Administración', 'url' => route('activity-logs.index'), 'search' => 'auditoria logs historial']
                : null,
        ]));

        // El filtro busca en 'search'; incluye la etiqueta para que también cuente.
        $commands = array_map(function ($c) {
            $c['search'] = trim($c['label'].' '.$c['group'].' '.$c['search']);
            return $c;
        }, $commands);
    @endphp

    <div
        x-data="commandPalette(@js(['commands' => $commands]))"
        x-on:keydown.escape.window="close()"
        x-on:open-command-palette.window="openPanel()"
    >
        <template x-teleport="body">
            <div
                x-show="open"
                x-cloak
                class="fixed inset-0 z-[100] flex items-start justify-center px-4 pt-[12vh]"
                role="dialog"
                aria-modal="true"
                aria-label="Paleta de comandos"
            >
                {{-- Fondo --}}
                <div
                    x-show="open"
                    x-transition.opacity
                    class="absolute inset-0 bg-black/40 backdrop-blur-[2px]"
                    x-on:click="close()"
                ></div>

                {{-- Panel --}}
                <div
                    x-show="open"
                    x-transition
                    class="relative w-full max-w-xl overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900"
                >
                    {{-- Buscador --}}
                    <div class="flex items-center gap-2 border-b border-neutral-100 px-4 dark:border-zinc-700">
                        <svg class="size-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z" />
                        </svg>
                        <input
                            x-ref="search"
                            x-model="search"
                            type="text"
                            autocomplete="off"
                            placeholder="Escribe para ir a un módulo…"
                            class="w-full bg-transparent py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none dark:text-white"
                            x-on:input="highlighted = 0"
                            x-on:keydown.arrow-down.prevent="move(1)"
                            x-on:keydown.arrow-up.prevent="move(-1)"
                            x-on:keydown.enter.prevent="onEnter()"
                        />
                        <kbd class="hidden shrink-0 rounded border border-neutral-200 px-1.5 py-0.5 text-[10px] font-medium text-gray-400 sm:block dark:border-zinc-600">Esc</kbd>
                    </div>

                    {{-- Resultados --}}
                    <ul x-ref="list" class="max-h-80 overflow-y-auto py-2 text-sm">
                        <template x-for="(cmd, i) in filtered" :key="cmd.url">
                            <li :data-index="i">
                                <a
                                    :href="cmd.url"
                                    x-on:click.prevent="go(cmd)"
                                    x-on:mouseenter="highlighted = i"
                                    :class="highlighted === i
                                        ? 'bg-blue-50 dark:bg-zinc-700'
                                        : ''"
                                    class="flex cursor-pointer items-center justify-between gap-3 px-4 py-2.5 transition-colors"
                                >
                                    <span class="truncate font-medium text-gray-800 dark:text-gray-100" x-text="cmd.label"></span>
                                    <span class="shrink-0 text-xs text-gray-400 dark:text-zinc-500" x-text="cmd.group"></span>
                                </a>
                            </li>
                        </template>

                        <li x-show="filtered.length === 0" class="px-4 py-6 text-center text-gray-400 dark:text-zinc-500">
                            Sin resultados
                        </li>
                    </ul>

                    {{-- Pie con ayuda --}}
                    <div class="flex items-center gap-3 border-t border-neutral-100 px-4 py-2 text-[11px] text-gray-400 dark:border-zinc-700 dark:text-zinc-500">
                        <span>↑ ↓ navegar</span>
                        <span>Enter abrir</span>
                        <span>Esc cerrar</span>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endif
