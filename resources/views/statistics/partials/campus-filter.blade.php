@php
    $user = auth()->user();
    $campuses = \App\Http\Controllers\StatisticsController::campusOptions();
    $activeCampusId = app(\App\Services\CampusScopeService::class)->activeCampusId($user);
@endphp

@if ($user?->isSuperadmin())
    <form
        method="POST"
        action="{{ route('statistics.campus') }}"
        data-statistics-campus-form
        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-end"
    >
        @csrf
        <label for="statistics-campus-id" class="text-xs font-medium text-gray-500 dark:text-gray-400">
            Sede activa
        </label>
        <select
            id="statistics-campus-id"
            name="campus_id"
            data-statistics-campus-select
            class="w-full rounded-lg border border-neutral-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-200 sm:w-56"
        >
            <option value="">Todas las sedes</option>
            @foreach ($campuses as $campusId => $campusName)
                <option value="{{ $campusId }}" @selected((int) $activeCampusId === (int) $campusId)>
                    {{ $campusName }}
                </option>
            @endforeach
        </select>
        <span
            data-statistics-campus-status
            class="hidden items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium shadow-sm transition-colors"
            role="status"
            aria-live="polite"
        >
            <span data-statistics-campus-status-icon class="flex size-3 items-center justify-center"></span>
            <span data-statistics-campus-status-text></span>
        </span>
    </form>

    @once
        <script>
            (() => {
                const eventName = 'statistics:campus-changed';

                document.addEventListener('change', async (event) => {
                    const select = event.target.closest('[data-statistics-campus-select]');
                    if (!select) return;

                    const form = select.closest('[data-statistics-campus-form]');
                    const status = form?.querySelector('[data-statistics-campus-status]');
                    const statusIcon = form?.querySelector('[data-statistics-campus-status-icon]');
                    const statusText = form?.querySelector('[data-statistics-campus-status-text]');
                    const formData = new FormData(form);

                    const setStatus = (type, text) => {
                        if (!status || !statusIcon || !statusText) return;

                        const styles = {
                            loading: [
                                'border-blue-200', 'bg-blue-50', 'text-blue-700',
                                'dark:border-blue-900', 'dark:bg-blue-950/40', 'dark:text-blue-300',
                            ],
                            success: [
                                'border-emerald-200', 'bg-emerald-50', 'text-emerald-700',
                                'dark:border-emerald-900', 'dark:bg-emerald-950/40', 'dark:text-emerald-300',
                            ],
                            error: [
                                'border-red-200', 'bg-red-50', 'text-red-700',
                                'dark:border-red-900', 'dark:bg-red-950/40', 'dark:text-red-300',
                            ],
                        };

                        status.className = [
                            'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium shadow-sm transition-colors',
                            ...styles[type],
                        ].join(' ');

                        statusIcon.innerHTML = type === 'loading'
                            ? '<span class="size-3 rounded-full border-2 border-current border-t-transparent animate-spin"></span>'
                            : type === 'success'
                                ? '<svg viewBox="0 0 16 16" class="size-3" fill="currentColor"><path fill-rule="evenodd" d="M13.78 3.72a.75.75 0 0 1 0 1.06l-6.25 6.25a.75.75 0 0 1-1.06 0L3.22 7.78a.75.75 0 0 1 1.06-1.06L7 9.44l5.72-5.72a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>'
                                : '<svg viewBox="0 0 16 16" class="size-3" fill="currentColor"><path fill-rule="evenodd" d="M8 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13ZM7.25 4.75a.75.75 0 0 1 1.5 0V8a.75.75 0 0 1-1.5 0V4.75ZM8 11.5a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z" clip-rule="evenodd"/></svg>';
                        statusText.textContent = text;
                    };

                    select.disabled = true;
                    setStatus('loading', 'Actualizando datos');

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) throw new Error(`HTTP ${response.status}`);

                        const payload = await response.json();

                        window.dispatchEvent(new CustomEvent(eventName, {
                            detail: { campusId: payload.campus_id ?? null },
                        }));

                        setStatus('success', 'Datos actualizados');
                        window.setTimeout(() => status?.classList.add('hidden'), 1800);
                    } catch (error) {
                        setStatus('error', 'No se pudo actualizar');
                    } finally {
                        select.disabled = false;
                    }
                });
            })();
        </script>
    @endonce
@endif
