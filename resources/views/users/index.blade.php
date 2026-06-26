<x-layouts.app :title="__('Users')">
    <div class="p-1 pb-8 sm:p-4 sm:pb-10 md:p-6 md:pb-12">
        <div class="flex h-max w-full flex-1 flex-col gap-4 rounded-2xl">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2" x-data="{ infoOpen: false }">
                    <flux:heading size="xl" level="1">
                        {{ __('Users list') }}
                    </flux:heading>
                    <div class="relative" @click.outside="infoOpen = false" @keydown.escape="infoOpen = false">
                        <button type="button" @click="infoOpen = !infoOpen"
                            :class="infoOpen ? 'text-[#3b82f6] bg-blue-50 dark:bg-blue-900/30' : 'text-gray-400 hover:text-[#3b82f6] hover:bg-blue-50 dark:hover:bg-blue-900/30'"
                            class="p-1 rounded-lg transition-colors cursor-pointer"
                            aria-label="Referencias de color">
                            <flux:icon.information-circle class="size-5" />
                        </button>
                        <div x-show="infoOpen" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute left-0 top-full mt-2 w-64 z-30 rounded-xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-4 shadow-lg">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-200 mb-2">Referencias de color</p>
                            <ul class="space-y-1.5 text-xs text-gray-600 dark:text-gray-300">
                                <li class="flex items-center gap-2"><span class="size-3 rounded-sm bg-[#e2a542]"></span> Rol del usuario</li>
                                <li class="flex items-center gap-2"><span class="size-3 rounded-sm bg-[#cc5e50]"></span> Dependencias asignadas</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <livewire:user.online-count />
            </div>

            @if(session('success'))
                <div id="users-success-alert"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm transition-opacity duration-500">
                    <flux:icon.check-circle class="size-5 shrink-0" />
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div
                x-data="usersIndexFilters({
                    initialFiltersOpen: @js($activeFilterCount > 0),
                    baseUrl: @js(route('users.index')),
                })"
                class="flex w-full flex-col gap-3">
                <form x-ref="form" method="GET" action="{{ route('users.index') }}" class="flex w-full flex-col gap-3" x-on:submit.prevent="applyFilters()">
                    <div class="flex w-full flex-col gap-3 rounded-2xl border border-neutral-200 bg-zinc-50 p-3 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:flex-row sm:items-center">
                        <div class="relative min-w-0 flex-1">
                            <svg class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-gray-400"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z" />
                            </svg>
                            <input
                                id="users-search-input"
                                type="search"
                                name="q"
                                value="{{ $search }}"
                                placeholder="Buscar usuario…"
                                x-on:input.debounce.600ms="applyFilters()"
                                x-on:keydown.enter.prevent="applyFilters()"
                                class="h-10 w-full rounded-lg border border-neutral-200 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" />
                        </div>

                        <button type="button" @click="filtersOpen = !filtersOpen"
                            :class="filtersOpen ? 'border-blue-400 bg-blue-50 text-blue-700 dark:border-blue-600 dark:bg-blue-950/50 dark:text-blue-300' : 'border-neutral-200 bg-white text-gray-600 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-400 dark:hover:bg-zinc-700'"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border px-3 text-sm font-medium shadow-sm transition-colors">
                            <flux:icon.funnel class="size-4" />
                            Filtros
                            <span x-show="activeFilterCount() > 0" x-cloak x-text="activeFilterCount()"
                                class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1 text-[11px] font-semibold leading-none text-white"></span>
                        </button>

                        <a href="{{ route('users.index') }}"
                            x-show="hasActiveCriteria()"
                            x-cloak
                            x-on:click.prevent="clearFilters()"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-neutral-200 bg-white px-3 text-sm font-medium text-gray-600 shadow-sm transition-colors hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-400 dark:hover:border-red-900 dark:hover:bg-red-950/30 dark:hover:text-red-400">
                            <flux:icon.x-mark class="size-4" />
                            Limpiar
                        </a>

                        <div class="sm:ml-auto">
                            <flux:modal.trigger name="create-user-modal">
                                <flux:button icon="user-plus" square
                                    class="cursor-pointer !h-10 !w-10 !bg-[#3b82f6] hover:!bg-blue-700 !text-white !border-transparent !shadow-sm"
                                    :aria-label="__('Add User')" :title="__('Add User')" />
                            </flux:modal.trigger>
                        </div>
                    </div>

                    <div x-show="filtersOpen" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="rounded-2xl border border-neutral-200 bg-zinc-50 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-1.5">
                                <label for="users-campus-filter" class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-zinc-400">Sede</label>
                                @php($campusFilterOptions = auth()->user()->isSuperadmin() ? ['global' => 'Global'] + $campuses : $campuses)
                                <x-ui.searchable-select
                                    id="users-campus-filter"
                                    name="campus_id"
                                    :value="$filters['campus_id']"
                                    :options="$campusFilterOptions"
                                    placeholder="Todas"
                                    empty-label="Todas"
                                    search-placeholder="Buscar sede..."
                                    x-on:change="applyFilters()" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="users-dependency-filter" class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-zinc-400">Dependencia</label>
                                <x-ui.searchable-select
                                    id="users-dependency-filter"
                                    name="dependency_id"
                                    :value="$filters['dependency_id']"
                                    :options="$filterDependencies"
                                    placeholder="Todas"
                                    empty-label="Todas"
                                    search-placeholder="Buscar dependencia..."
                                    x-on:change="applyFilters()" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="users-role-filter" class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-zinc-400">Rol</label>
                                <select id="users-role-filter" name="role" x-on:change="applyFilters()"
                                    class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-sm text-gray-900 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <option value="">Todos</option>
                                    @foreach($roles as $value => $label)
                                        <option value="{{ $value }}" @selected($filters['role'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="users-status-filter" class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-zinc-400">Estado</label>
                                <select id="users-status-filter" name="status" x-on:change="applyFilters()"
                                    class="h-10 rounded-lg border border-neutral-200 bg-white px-3 text-sm text-gray-900 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <option value="">Todos</option>
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="relative" x-on:click="handleTableClick($event)">
                    <div x-show="loading" x-cloak class="absolute inset-0 z-20 flex items-start justify-center rounded-2xl bg-white/70 pt-16 backdrop-blur-[1px] dark:bg-zinc-900/70">
                        <span class="inline-flex items-center gap-2 rounded-full border border-neutral-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            <svg class="size-4 animate-spin text-[#3b82f6]" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                            </svg>
                            Actualizando usuarios...
                        </span>
                    </div>
                    <div x-ref="tableRegion" id="users-table-region">
                        @include('users.partials.table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('usersIndexFilters', ({ initialFiltersOpen, baseUrl }) => ({
                filtersOpen: initialFiltersOpen,
                loading: false,
                criteriaVersion: 0,
                requestController: null,

                formUrl({ resetPage = true } = {}) {
                    const formData = new FormData(this.$refs.form);
                    const params = new URLSearchParams();

                    for (const [key, value] of formData.entries()) {
                        if (value !== '') params.set(key, value);
                    }

                    if (resetPage) params.delete('page');

                    const query = params.toString();
                    return query ? `${baseUrl}?${query}` : baseUrl;
                },

                partialUrl(url) {
                    const next = new URL(url, window.location.origin);
                    next.searchParams.set('partial', '1');
                    return next.toString();
                },

                activeFilterCount() {
                    this.criteriaVersion;
                    const formData = new FormData(this.$refs.form);
                    return ['campus_id', 'dependency_id', 'role', 'status']
                        .filter((key) => (formData.get(key) || '') !== '')
                        .length;
                },

                hasActiveCriteria() {
                    this.criteriaVersion;
                    const formData = new FormData(this.$refs.form);
                    return (formData.get('q') || '') !== '' || this.activeFilterCount() > 0;
                },

                applyFilters() {
                    this.criteriaVersion++;
                    this.loadTable(this.formUrl({ resetPage: true }), 'replace');
                },

                clearFilters() {
                    this.$refs.form.reset();
                    for (const field of this.$refs.form.elements) {
                        if (field.name) field.value = '';
                    }
                    window.dispatchEvent(new CustomEvent('searchable-select-sync', {
                        detail: { values: { campus_id: '', dependency_id: '' } },
                    }));
                    this.criteriaVersion++;
                    this.loadTable(baseUrl, 'replace');
                },

                handleTableClick(event) {
                    const link = event.target.closest('a[href]');
                    if (!link || !this.$refs.tableRegion.contains(link)) return;

                    const url = new URL(link.href, window.location.origin);
                    if (url.pathname !== new URL(baseUrl, window.location.origin).pathname) return;

                    event.preventDefault();
                    this.loadTable(url.toString(), 'push');
                },

                syncFormFromUrl(url) {
                    const params = new URL(url, window.location.origin).searchParams;
                    for (const field of this.$refs.form.elements) {
                        if (field.name) field.value = params.get(field.name) || '';
                    }
                    window.dispatchEvent(new CustomEvent('searchable-select-sync', {
                        detail: {
                            values: {
                                campus_id: params.get('campus_id') || '',
                                dependency_id: params.get('dependency_id') || '',
                            },
                        },
                    }));
                    this.criteriaVersion++;
                },

                async loadTable(url, historyMode = false) {
                    this.requestController?.abort();
                    this.requestController = new AbortController();
                    this.loading = true;

                    try {
                        const response = await fetch(this.partialUrl(url), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html',
                            },
                            signal: this.requestController.signal,
                        });

                        if (!response.ok) throw new Error(`HTTP ${response.status}`);

                        this.$refs.tableRegion.innerHTML = await response.text();
                        window.Alpine?.initTree(this.$refs.tableRegion);

                        if (historyMode === 'push') {
                            window.history.pushState({}, '', url);
                        } else if (historyMode === 'replace') {
                            window.history.replaceState({}, '', url);
                        }
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            window.location.href = url;
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                init() {
                    window.addEventListener('popstate', () => {
                        this.syncFormFromUrl(window.location.href);
                        this.loadTable(window.location.href, false);
                    });
                },
            }));
        });
    </script>

    @livewire('user.create-user-modal', ['dependencies' => $dependencies, 'campuses' => $campuses, 'roles' => $roles])
    @livewire('user.edit-user-modal', ['dependencies' => $dependencies, 'campuses' => $campuses, 'roles' => $roles])

</x-layouts.app>
