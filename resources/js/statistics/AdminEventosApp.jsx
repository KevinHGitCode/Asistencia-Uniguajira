import React, { useEffect, useState, useCallback, useRef, useMemo } from "react";

import { useTheme }        from "./hooks/useTheme.js";
import { useAdminEventos } from "./hooks/useAdminEventos.js";

import { CalendarIcon }      from "./components/AdminEventosIcons.jsx";
import AdminFiltersPanel     from "./components/AdminFiltersPanel.jsx";
import ActiveFilters         from "./components/ActiveFilters.jsx";
import EventCard, { EventCardSkeleton } from "./components/EventCard.jsx";
import Pagination            from "./components/Pagination.jsx";

const PER_PAGE = 9;

export default function AdminEventosApp() {
    useTheme();
    const { state, fetchAll } = useAdminEventos();

    // ── Filtros ──
    const [from, setFrom]             = useState("");
    const [to, setTo]                 = useState("");
    const [search, setSearch]         = useState("");
    const [searchInput, setSearchInput] = useState("");
    const [selectedDeps, setSelectedDeps]   = useState([]);
    const [selectedUsers, setSelectedUsers] = useState([]);

    // ── Paginación ──
    const [currentPage, setCurrentPage] = useState(1);
    const gridRef     = useRef(null);
    const debounceRef = useRef(null);

    // Debounce búsqueda
    const handleSearchChange = useCallback((e) => {
        const value = e.target.value;
        setSearchInput(value);
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => setSearch(value), 400);
    }, []);

    // Filtros combinados
    const filters = useMemo(
        () => ({ from, to, search, dependencies: selectedDeps, users: selectedUsers }),
        [from, to, search, selectedDeps, selectedUsers],
    );

    // Reset página al cambiar filtros
    useEffect(() => { setCurrentPage(1); }, [from, to, search, selectedDeps, selectedUsers]);

    // Fetch
    useEffect(() => { fetchAll(filters); }, [filters, fetchAll]);

    const { events, total, loading, error, filterOptions } = state;

    // ── Paginación calculada ──
    const totalPages      = Math.max(1, Math.ceil(events.length / PER_PAGE));
    const safePage        = Math.min(currentPage, totalPages);
    const startIdx        = (safePage - 1) * PER_PAGE;
    const paginatedEvents = events.slice(startIdx, startIdx + PER_PAGE);

    const handlePageChange = useCallback((page) => {
        setCurrentPage(page);
        gridRef.current?.scrollIntoView({ behavior: "smooth", block: "start" });
    }, []);

    // ── Handlers limpiar filtros ──
    const clearDates  = () => { setFrom(""); setTo(""); };
    const clearSearch = () => { setSearch(""); setSearchInput(""); };
    const removeDep   = (id) => setSelectedDeps((p) => p.filter((d) => d !== id));
    const removeUser  = (id) => setSelectedUsers((p) => p.filter((u) => u !== id));

    const hasAnyFilter = from || to || search || selectedDeps.length || selectedUsers.length;
    const clearAll = () => { clearDates(); clearSearch(); setSelectedDeps([]); setSelectedUsers([]); };

    return (
        <div>
            {/* ── Filtros ── */}
            <AdminFiltersPanel
                searchInput={searchInput} onSearchChange={handleSearchChange}
                from={from} onFromChange={setFrom}
                to={to}     onToChange={setTo}
                filterOptions={filterOptions}
                selectedDeps={selectedDeps}   onDepsChange={setSelectedDeps}
                selectedUsers={selectedUsers} onUsersChange={setSelectedUsers}
                hasAnyFilter={hasAnyFilter}   onClearAll={clearAll}
            />

            {/* ── Badges ── */}
            <ActiveFilters
                filters={filters}
                depOptions={filterOptions.dependencies}
                userOptions={filterOptions.users}
                onRemoveDep={removeDep}
                onRemoveUser={removeUser}
                onClearDates={clearDates}
                onClearSearch={clearSearch}
            />

            {/* ── Contador ── */}
            <div className="mb-6">
                <div className="flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700 rounded-2xl shadow-sm max-w-xs">
                    <div className="p-2 rounded-lg bg-amber-100 dark:bg-amber-900/30 shrink-0">
                        <span className="text-amber-600 dark:text-amber-400">
                            <CalendarIcon className="w-5 h-5" />
                        </span>
                    </div>
                    <div>
                        <p className="text-lg font-bold text-gray-900 dark:text-gray-100 leading-tight">
                            {loading
                                ? <span className="block w-14 h-5 bg-gray-200 dark:bg-zinc-700 rounded-md animate-pulse" />
                                : (total ?? 0).toLocaleString("es-CO")
                            }
                        </p>
                        <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total de eventos</p>
                    </div>
                </div>
            </div>

            {/* ── Grid ── */}
            <div ref={gridRef} className="relative flex w-full flex-1 flex-col gap-4 p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
                <div className="flex items-center justify-between mb-4">
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
                        <CalendarIcon className="inline-block w-6 h-6 mr-2" />
                        Todos los Eventos
                    </h2>
                    <span className="px-3 py-1 text-sm font-medium bg-[#e2a542] text-white rounded-2xl">
                        {loading ? "…" : `${total} ${total === 1 ? "evento" : "eventos"}`}
                    </span>
                </div>

                {loading && (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {Array.from({ length: PER_PAGE }).map((_, i) => <EventCardSkeleton key={i} />)}
                    </div>
                )}

                {!loading && error && (
                    <div className="text-center py-8 text-red-500">
                        <p className="text-lg font-medium">Error al cargar los eventos</p>
                        <p className="text-sm mt-1">{error}</p>
                    </div>
                )}

                {!loading && !error && events.length === 0 && (
                    <div className="text-center py-8 text-gray-500 dark:text-gray-400">
                        <CalendarIcon className="mx-auto h-12 w-12 mb-3" />
                        <p className="text-lg font-medium">No se encontraron eventos</p>
                        <p className="text-sm mt-1">Intenta ajustar los filtros o el término de búsqueda</p>
                    </div>
                )}

                {!loading && !error && events.length > 0 && (
                    <>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {paginatedEvents.map((event) => <EventCard key={event.id} event={event} />)}
                        </div>

                        <div className="mt-2">
                            <p className="text-xs text-center text-gray-400 dark:text-gray-500 mb-1">
                                Mostrando {startIdx + 1}–{Math.min(startIdx + PER_PAGE, events.length)} de {events.length} eventos
                            </p>
                            <Pagination
                                currentPage={safePage}
                                totalPages={totalPages}
                                onPageChange={handlePageChange}
                            />
                        </div>
                    </>
                )}
            </div>
        </div>
    );
}