<x-layouts.app :title="__('Users')">
    <div class="mb-4">
        <div class="flex h-max w-full flex-1 flex-col gap-4 rounded-2xl">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <flux:heading size="xl" level="1">
                    {{ __('Users list') }}
                </flux:heading>
            </div>

            @if(session('success'))
                <div class="rounded-lg bg-green-100 border border-green-400 text-green-700 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="w-full">
                <div class="flex w-full flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <flux:input
                            id="users-search-input"
                            type="search"
                            name="q"
                            :label="__('Search users')"
                            :placeholder="__('Name, email, role or dependency')"
                            :value="$search" />
                    </div>
                    <div class="sm:pt-7">
                        <flux:modal.trigger name="create-user-modal">
                            <flux:button
                                variant="primary"
                                class="border hover:scale-105 transition-transform w-full sm:w-auto">
                                {{ __('Add User') }}
                            </flux:button>
                        </flux:modal.trigger>
                    </div>
                </div>
            </div>

            <div class="relative h-full flex-1 rounded-2xl border bg-zinc-50 dark:bg-zinc-900 border-neutral-200 dark:border-neutral-700">
                <div class="hidden md:block p-4">
                    <div class="overflow-x-auto overflow-y-visible rounded-xl border border-neutral-200 dark:border-neutral-700">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-zinc-100 dark:bg-zinc-800">
                                <tr class="text-left text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-300">
                                    <th class="px-4 py-3">{{ __('User') }}</th>
                                    <th class="px-4 py-3">{{ __('Role') }}</th>
                                    <th class="px-4 py-3">{{ __('Dependencies') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700 bg-white dark:bg-zinc-900">
                                @forelse ($users as $user)
                                    @php
                                        $dependencyNames = $user->dependencies->pluck('name')->values();
                                        $primaryDependency = $dependencyNames->first();
                                        $extraDependenciesCount = max(0, $dependencyNames->count() - 1);
                                        $searchText = \Illuminate\Support\Str::lower(implode(' ', [
                                            $user->name,
                                            $user->email,
                                            $user->role,
                                            $dependencyNames->implode(' '),
                                            (string) $user->events_count,
                                        ]));
                                    @endphp
                                    <tr
                                        class="hover:bg-zinc-50 dark:hover:bg-zinc-800/70 transition-colors"
                                        data-user-search="{{ $searchText }}">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3 min-w-[16rem]">
                                                @if($user->avatar)
                                                    <img
                                                        src="{{ Storage::url($user->avatar) }}"
                                                        alt="{{ $user->name }}"
                                                        class="h-9 w-9 rounded-full object-cover border border-neutral-200 dark:border-neutral-600">
                                                @else
                                                    <div class="h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700 border border-neutral-200 dark:border-neutral-600 flex items-center justify-center">
                                                        <span class="text-sm font-bold uppercase text-gray-800 dark:text-white">
                                                            {{ substr($user->name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                                                    <p class="truncate text-sm text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
                                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                                        @if($user->events_count === 1)
                                                            {{ __(':count event created', ['count' => $user->events_count]) }}
                                                        @else
                                                            {{ __(':count events created', ['count' => $user->events_count]) }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(isset($user->role))
                                                <flux:badge class="!bg-[#e2a542] !text-white" :color="null">
                                                    {{ __(ucfirst($user->role)) }}
                                                </flux:badge>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 max-w-sm">
                                            @if(isset($user->role) && $user->role === 'user')
                                                @if($user->dependencies->isNotEmpty())
                                                    <div class="flex items-center gap-2">
                                                        <flux:badge class="!bg-[#cc5e50] !text-white max-w-[14rem] truncate" :color="null" :title="$primaryDependency">
                                                            {{ \Illuminate\Support\Str::limit($primaryDependency, 30, '...') }}
                                                        </flux:badge>
                                                        @if($extraDependenciesCount > 0)
                                                            <div class="relative group/dependencies">
                                                                <button
                                                                    type="button"
                                                                    class="inline-flex items-center rounded-full bg-[#cc5e50] px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-[#cc5e50]/40"
                                                                    aria-label="Mostrar dependencias adicionales">
                                                                    +{{ $extraDependenciesCount }}
                                                                </button>

                                                                <div class="dependency-tooltip pointer-events-none absolute right-0 top-full z-20 mt-2 hidden min-w-56 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200 group-hover/dependencies:block group-focus-within/dependencies:block">
                                                                    <p class="mb-2 font-semibold">Dependencias</p>
                                                                    <ul class="space-y-1">
                                                                        @foreach ($dependencyNames as $dependencyName)
                                                                            <li class="truncate" title="{{ $dependencyName }}">{{ $dependencyName }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Not assigned') }}</span>
                                                @endif
                                            @else
                                                <span class="text-sm text-zinc-500 dark:text-zinc-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-1">
                                                <a href="{{ route('user.edit', ['id' => $user->id]) }}">
                                                    <flux:button
                                                        square
                                                        variant="ghost"
                                                        size="sm"
                                                        title="{{ __('Edit user') }}"
                                                        class="hover:text-[#62a9b6] transition-colors hover:cursor-pointer">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L12 15l-4 1 1-4 8.586-8.586z" />
                                                        </svg>
                                                    </flux:button>
                                                </a>
                                                <a href="{{ route('users.information', ['id' => $user->id]) }}">
                                                    <flux:button
                                                        square
                                                        variant="ghost"
                                                        size="sm"
                                                        title="{{ __('View information') }}"
                                                        class="hover:text-[#e2a542] transition-colors hover:cursor-pointer">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </flux:button>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ __('No users found') }}
                                        </td>
                                    </tr>
                                @endforelse
                                <tr id="users-empty-desktop" class="hidden">
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ __('No users found') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="users-table-pagination" class="mt-3 flex items-center justify-between gap-3 px-1">
                        <p id="users-table-page-info" class="text-xs text-zinc-600 dark:text-zinc-400"></p>
                        <div class="flex items-center gap-3">
                            <label for="users-page-size" class="text-xs text-zinc-600 dark:text-zinc-400">Por página</label>
                            <select
                                id="users-page-size"
                                class="rounded-md border border-neutral-300 bg-white px-2 py-1.5 text-xs text-zinc-700 dark:border-neutral-600 dark:bg-zinc-900 dark:text-zinc-200">
                                <option value="5" selected>5</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                            </select>
                            <button
                                id="users-page-prev"
                                type="button"
                                aria-label="Página anterior"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-300 text-zinc-700 transition-colors hover:bg-zinc-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-neutral-600 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.78 4.22a.75.75 0 010 1.06L8.06 10l4.72 4.72a.75.75 0 11-1.06 1.06l-5.25-5.25a.75.75 0 010-1.06l5.25-5.25a.75.75 0 011.06 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button
                                id="users-page-next"
                                type="button"
                                aria-label="Página siguiente"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-300 text-zinc-700 transition-colors hover:bg-zinc-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-neutral-600 dark:text-zinc-200 dark:hover:bg-zinc-800">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.22 15.78a.75.75 0 010-1.06L11.94 10 7.22 5.28a.75.75 0 111.06-1.06l5.25 5.25a.75.75 0 010 1.06l-5.25 5.25a.75.75 0 01-1.06 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-4 grid grid-cols-1 gap-4 md:hidden">
                    @forelse ($users as $user)
                        @php
                            $dependencyNames = $user->dependencies->pluck('name')->values();
                            $mobileSearchText = \Illuminate\Support\Str::lower(implode(' ', [
                                $user->name,
                                $user->email,
                                $user->role,
                                $dependencyNames->implode(' '),
                                (string) $user->events_count,
                            ]));
                        @endphp
                        <div data-user-search="{{ $mobileSearchText }}">
                            @livewire('user.card', [
                                'title' => $user->name,
                                'user' => $user,
                                'showDependenciesUpward' => $loop->last,
                            ], key($user->id))
                        </div>
                    @empty
                        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('No users found') }}
                        </div>
                    @endforelse
                    <div id="users-empty-mobile" class="hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('No users found') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Leyenda -->
    <div class="z-10 flex w-full flex-1 flex-col gap-4 p-6 mb-4 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
        <div class="flex items-center justify-center gap-4 sm:gap-8 flex-wrap">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-[#e2a542]"></div>
                <span class="text-xs sm:text-sm text-black dark:text-white">Rol</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-[#cc5e50]"></div>
                <span class="text-xs sm:text-sm text-black dark:text-white">Dependencias</span>
            </div>
        </div>
    </div>

    @livewire('user.create-user-modal', ['dependencies' => $dependencies, 'roles' => $roles])

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('users-search-input');
            if (!input) return;

            const desktopRows = Array.from(document.querySelectorAll('tr[data-user-search]'));
            const mobileCards = Array.from(document.querySelectorAll('div[data-user-search]'));
            const desktopEmpty = document.getElementById('users-empty-desktop');
            const mobileEmpty = document.getElementById('users-empty-mobile');
            const paginationContainer = document.getElementById('users-table-pagination');
            const prevButton = document.getElementById('users-page-prev');
            const nextButton = document.getElementById('users-page-next');
            const pageInfo = document.getElementById('users-table-page-info');
            const pageSizeSelect = document.getElementById('users-page-size');

            let timer = null;
            let currentPage = 1;
            let rowsPerPage = Number(pageSizeSelect?.value || 10);

            const normalize = (value) => (value || '').toLowerCase().trim();

            const applyFilter = () => {
                const query = normalize(input.value);
                let visibleMobile = 0;
                const filteredDesktopRows = desktopRows.filter((row) => {
                    const text = normalize(row.getAttribute('data-user-search'));
                    return query === '' || text.includes(query);
                });
                const totalPages = Math.max(1, Math.ceil(filteredDesktopRows.length / rowsPerPage));
                currentPage = Math.min(currentPage, totalPages);
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                const visibleDesktopRows = filteredDesktopRows.slice(start, end);

                desktopRows.forEach((row) => {
                    const show = visibleDesktopRows.includes(row);
                    row.classList.toggle('hidden', !show);
                });

                visibleDesktopRows.forEach((row, index) => {
                    const tooltips = Array.from(row.querySelectorAll('.dependency-tooltip'));
                    const showUpward = index === visibleDesktopRows.length - 1;
                    tooltips.forEach((tooltip) => {
                        tooltip.classList.toggle('top-full', !showUpward);
                        tooltip.classList.toggle('mt-2', !showUpward);
                        tooltip.classList.toggle('bottom-full', showUpward);
                        tooltip.classList.toggle('mb-2', showUpward);
                    });
                });

                mobileCards.forEach((card) => {
                    const text = normalize(card.getAttribute('data-user-search'));
                    const show = query === '' || text.includes(query);
                    card.classList.toggle('hidden', !show);
                    if (show) visibleMobile++;
                });

                if (desktopEmpty) desktopEmpty.classList.toggle('hidden', filteredDesktopRows.length > 0);
                if (mobileEmpty) mobileEmpty.classList.toggle('hidden', visibleMobile > 0);

                if (paginationContainer) {
                    paginationContainer.classList.toggle('hidden', filteredDesktopRows.length === 0);
                }
                if (pageInfo) {
                    pageInfo.textContent = `Página ${currentPage} de ${totalPages} (${filteredDesktopRows.length} usuarios)`;
                }
                if (prevButton) prevButton.disabled = currentPage <= 1;
                if (nextButton) nextButton.disabled = currentPage >= totalPages;
            };

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    currentPage = 1;
                    applyFilter();
                }, 600);
            });

            if (prevButton) {
                prevButton.addEventListener('click', () => {
                    if (currentPage > 1) {
                        currentPage--;
                        applyFilter();
                    }
                });
            }

            if (nextButton) {
                nextButton.addEventListener('click', () => {
                    currentPage++;
                    applyFilter();
                });
            }

            if (pageSizeSelect) {
                pageSizeSelect.addEventListener('change', () => {
                    rowsPerPage = Number(pageSizeSelect.value || 10);
                    currentPage = 1;
                    applyFilter();
                });
            }

            applyFilter();
        });
    </script>
</x-layouts.app>
