import React, { useEffect, useState, useCallback, useRef, useMemo } from "react";

import { useTheme }        from "./hooks/useTheme.js";
import { useAdminEventos } from "./hooks/useAdminEventos.js";

import { CalendarIcon }      from "./components/AdminEventosIcons.jsx";
import AdminFiltersPanel     from "./components/AdminFiltersPanel.jsx";
import ActiveFilters         from "./components/ActiveFilters.jsx";
import { EventCardSkeleton } from "./components/EventCard.jsx";
import EventSection          from "./components/EventSection.jsx";

// ── Íconos de sección ──

const ClockSectionIcon = (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
);

const TrendUpSectionIcon = (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round"
            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
    </svg>
);

const CheckCircleSectionIcon = (
    <svg className="w-6 h-6" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
);

// ── Helpers para clasificar eventos ──

function classifyEvents(events) {
    const now = new Date();

    const inProgress = [];
    const upcoming   = [];
    const finished   = [];

    events.forEach((event) => {
        if (!event.date) {
            upcoming.push(event);
            return;
        }

        const startStr = `${event.date}T${event.start_time || "00:00:00"}`;
        const endStr   = `${event.date}T${event.end_time   || "23:59:59"}`;
        const start    = new Date(startStr);
        const end      = new Date(endStr);

        if (now >= start && now <= end) {
            inProgress.push(event);
        } else if (now < start) {
            upcoming.push(event);
        } else {
            finished.push(event);
        }
    });

    // En proceso y próximos: ordenar ascendente (más cercano primero)
    inProgress.sort((a, b) => `${a.date} ${a.start_time}`.localeCompare(`${b.date} ${b.start_time}`));
    upcoming.sort((a, b)   => `${a.date} ${a.start_time}`.localeCompare(`${b.date} ${b.start_time}`));
    // Finalizados: ordenar descendente (más reciente primero)
    finished.sort((a, b)   => `${b.date} ${b.end_time}`.localeCompare(`${a.date} ${a.end_time}`));

    return { inProgress, upcoming, finished };
}

// ── Componente principal ──

export default function AdminEventosApp() {
    useTheme();
    const { state, fetchAll } = useAdminEventos();

    // Filtros
    const [from, setFrom]               = useState("");
    const [to, setTo]                   = useState("");
    const [search, setSearch]           = useState("");
    const [searchInput, setSearchInput] = useState("");
    const [selectedDeps, setSelectedDeps]     = useState([]);
    const [selectedUsers, setSelectedUsers]   = useState([]);

    const debounceRef = useRef(null);

    const handleSearchChange = useCallback((e) => {
        const value = e.target.value;
        setSearchInput(value);
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => setSearch(value), 400);
    }, []);

    const filters = useMemo(
        () => ({ from, to, search, dependencies: selectedDeps, users: selectedUsers }),
        [from, to, search, selectedDeps, selectedUsers],
    );

    useEffect(() => { fetchAll(filters); }, [filters, fetchAll]);

    const { events, total, loading, error, filterOptions } = state;

    // Clasificar eventos
    const { inProgress, upcoming, finished } = useMemo(
        () => classifyEvents(events),
        [events],
    );

    // Handlers limpiar filtros
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

            {/* ── Loading ── */}
            {loading && (
                <div className="relative flex w-full flex-col gap-4 p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {Array.from({ length: 9 }).map((_, i) => <EventCardSkeleton key={i} />)}
                    </div>
                </div>
            )}

            {/* ── Error ── */}
            {!loading && error && (
                <div className="text-center py-8 text-red-500">
                    <p className="text-lg font-medium">Error al cargar los eventos</p>
                    <p className="text-sm mt-1">{error}</p>
                </div>
            )}

            {/* ── Secciones ── */}
            {!loading && !error && (
                <div className="space-y-6">
                    <EventSection
                        title="Eventos en Proceso"
                        icon={ClockSectionIcon}
                        events={inProgress}
                        emptyMessage="No hay eventos en proceso"
                        emptyHint="Los eventos que estén en curso aparecerán aquí"
                    />

                    <EventSection
                        title="Eventos Próximos"
                        icon={TrendUpSectionIcon}
                        events={upcoming}
                        emptyMessage="No hay eventos próximos"
                        emptyHint="Los eventos futuros aparecerán aquí"
                    />

                    <EventSection
                        title="Eventos Finalizados"
                        icon={CheckCircleSectionIcon}
                        events={finished}
                        emptyMessage="No hay eventos finalizados"
                        emptyHint="Los eventos completados aparecerán aquí"
                    />
                </div>
            )}
        </div>
    );
}