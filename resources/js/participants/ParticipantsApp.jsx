import { useState, useEffect, useMemo, useCallback, useRef } from 'react';
import { useParticipants } from './hooks/useParticipants.js';
import FiltersPanel from './components/FiltersPanel.jsx';
import ParticipantsTable from './components/ParticipantsTable.jsx';
import Pagination from './components/Pagination.jsx';
import { SearchIcon, FunnelIcon, ChevronDownIcon, SpinnerIcon } from './icons.jsx';

const EMPTY_FILTERS = { estamento: '', programa: '', dependencia: '', vinculacion: '', correo: '', sinClasificar: false };

function initialState() {
    const sp = new URLSearchParams(window.location.search);
    return {
        search: sp.get('q') ?? '',
        filters: {
            estamento: sp.get('estamento') ?? '',
            programa: sp.get('programa') ?? '',
            dependencia: sp.get('dependencia') ?? '',
            vinculacion: sp.get('vinculacion') ?? '',
            correo: sp.get('correo') ?? '',
            sinClasificar: sp.get('filtro') === 'sin_clasificar',
        },
    };
}

export default function ParticipantsApp() {
    const init = useRef(initialState());
    const { data, meta, loading, options, fetchList } = useParticipants();

    const [search, setSearch] = useState(init.current.search);
    const [debouncedSearch, setDebouncedSearch] = useState(init.current.search);
    const [filters, setFilters] = useState(init.current.filters);
    const [page, setPage] = useState(1);
    const [refreshTick, setRefreshTick] = useState(0);

    const activeFilterCount = useMemo(() => {
        let n = ['estamento', 'programa', 'dependencia', 'vinculacion', 'correo'].filter((k) => filters[k] !== '').length;
        if (filters.sinClasificar) n += 1;
        return n;
    }, [filters]);

    const hasActiveFilters = activeFilterCount > 0;
    const [filtersOpen, setFiltersOpen] = useState(hasActiveFilters);

    // Debounce de la búsqueda de texto
    useEffect(() => {
        const t = setTimeout(() => setDebouncedSearch(search), 300);
        return () => clearTimeout(t);
    }, [search]);

    // Volver a la primera página cuando cambian búsqueda o filtros
    useEffect(() => {
        setPage(1);
    }, [debouncedSearch, filters]);

    // Petición de datos
    useEffect(() => {
        fetchList({
            search: debouncedSearch,
            estamento: filters.estamento,
            programa: filters.programa,
            dependencia: filters.dependencia,
            vinculacion: filters.vinculacion,
            correo: filters.correo,
            sinClasificar: filters.sinClasificar,
            page,
            perPage: 25,
        });
    }, [debouncedSearch, filters, page, refreshTick, fetchList]);

    // Reflejar búsqueda/filtros/página en la URL (preservando otros params como ?tab)
    useEffect(() => {
        const sp = new URLSearchParams(window.location.search);
        const setOrDel = (key, val) => (val ? sp.set(key, val) : sp.delete(key));
        setOrDel('q', debouncedSearch);
        setOrDel('estamento', filters.estamento);
        setOrDel('programa', filters.programa);
        setOrDel('dependencia', filters.dependencia);
        setOrDel('vinculacion', filters.vinculacion);
        setOrDel('correo', filters.correo);
        setOrDel('filtro', filters.sinClasificar ? 'sin_clasificar' : '');
        setOrDel('page', page > 1 ? String(page) : '');
        const qs = sp.toString();
        window.history.replaceState({}, '', qs ? `${window.location.pathname}?${qs}` : window.location.pathname);
    }, [debouncedSearch, filters, page]);

    // Puente con Livewire: refrescar el listado tras editar/eliminar
    useEffect(() => {
        const refetch = () => setRefreshTick((t) => t + 1);
        const attach = () => window.Livewire?.on('participants-refresh', refetch);
        if (window.Livewire) attach();
        else document.addEventListener('livewire:init', attach, { once: true });
        return () => document.removeEventListener('livewire:init', attach);
    }, []);

    const setFilter = useCallback((key, value) => {
        setFilters((prev) => ({ ...prev, [key]: value }));
    }, []);

    const resetAll = useCallback(() => {
        setSearch('');
        setFilters(EMPTY_FILTERS);
    }, []);

    const onEdit = useCallback((id) => window.Livewire?.dispatch('open-edit-participant', { id }), []);
    const onDelete = useCallback((id, name) => window.Livewire?.dispatch('open-delete-participant', { id, name }), []);

    const total = meta?.total ?? 0;
    const from = meta?.from ?? 0;
    const to = meta?.to ?? 0;

    return (
        <div>
            {/* Barra de herramientas: búsqueda + filtros colapsables */}
            <div className="mb-3">
                <div className="flex flex-col sm:flex-row gap-2">
                    <div className="relative flex-1">
                        <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <SearchIcon className="size-4" />
                        </span>
                        <input type="text" value={search} onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar por nombre, documento o correo…"
                            className="w-full pl-9 pr-4 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                    </div>

                    <button type="button" onClick={() => setFiltersOpen((o) => !o)}
                        className={`inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg border text-sm font-medium transition-colors shrink-0 ${filtersOpen
                            ? 'border-blue-400 bg-blue-50 text-blue-700 dark:border-blue-600 dark:bg-blue-900/30 dark:text-blue-300'
                            : 'border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-700'}`}>
                        <FunnelIcon className="size-4" />
                        Filtros
                        {activeFilterCount > 0 && (
                            <span className="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full bg-blue-600 text-white text-[11px] font-semibold leading-none">{activeFilterCount}</span>
                        )}
                        <ChevronDownIcon className={`size-4 transition-transform ${filtersOpen ? 'rotate-180' : ''}`} />
                    </button>
                </div>

                {filtersOpen && (
                    <FiltersPanel filters={filters} options={options} onChange={setFilter} onReset={resetAll}
                        showReset={hasActiveFilters || search !== ''} />
                )}
            </div>

            {/* Resumen + paginación (arriba) */}
            {total > 0 && (
                <div className="mt-5 mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-xs text-gray-500 dark:text-zinc-400 shrink-0">
                        Mostrando <span className="font-medium text-gray-700 dark:text-zinc-200">{from.toLocaleString('es-CO')}</span> a{' '}
                        <span className="font-medium text-gray-700 dark:text-zinc-200">{to.toLocaleString('es-CO')}</span> de{' '}
                        <span className="font-medium text-gray-700 dark:text-zinc-200">{total.toLocaleString('es-CO')}</span>{' '}
                        {total === 1 ? 'participante' : 'participantes'}
                        {filters.sinClasificar && ' · solo sin clasificar'}
                    </p>
                    <Pagination currentPage={meta?.current_page ?? 1} lastPage={meta?.last_page ?? 1} onPageChange={setPage} />
                </div>
            )}

            {/* Tabla + overlay de carga */}
            <div className="relative">
                {loading && (
                    <div className="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/70 dark:bg-zinc-900/70">
                        <span className="inline-flex items-center gap-2 rounded-full border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-zinc-300 shadow-sm">
                            <SpinnerIcon className="size-4 text-[#3b82f6]" />
                            Filtrando…
                        </span>
                    </div>
                )}
                <ParticipantsTable rows={data} hasFilters={hasActiveFilters} search={debouncedSearch}
                    onEdit={onEdit} onDelete={onDelete} />
            </div>
        </div>
    );
}
