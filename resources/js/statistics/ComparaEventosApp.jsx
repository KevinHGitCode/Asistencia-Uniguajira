import React, { useEffect, useRef, useState, useCallback } from 'react';

import { useTheme }   from './hooks/useTheme.js';
import { useFilters }  from './hooks/useFilters.js';
import { FiltersPanel } from './components/FiltersPanel.jsx';
import { ChartCard }    from './components/ChartCard.jsx';

import { ProgramAttendancesBar } from './charts/ProgramAttendancesBar.jsx';
import { StackedCompareBar }     from './charts/StackedCompareBar.jsx';
import { ProgramParticipantsPie } from './charts/ProgramParticipantsPie.jsx';

// ── Helpers ──────────────────────────────────────────────────────────────────

/** Convierte filas planas { event_id, event_title, label, count } en
 *  formato recharts: [{ name, [cat]: N, ... }] + lista de categorías. */
function pivotDemo(rows, orderedEvents) {
  const categories = [...new Set(rows.map(r => r.label))].sort();
  const byEvent = {};
  rows.forEach(r => {
    if (!byEvent[r.event_id]) byEvent[r.event_id] = { name: r.event_title };
    byEvent[r.event_id][r.label] = r.count;
  });

  const data = orderedEvents.map(e => ({
    name: e.title,
    ...Object.fromEntries(categories.map(c => [c, 0])), // defaults
    ...(byEvent[e.id] || {}),
  }));

  return { data, categories };
}

function fmtDate(iso) {
  if (!iso) return '';
  const [y, m, d] = iso.split('-');
  return `${d}/${m}/${y}`;
}

// ── Icono check / uncheck ────────────────────────────────────────────────────

function CheckIcon({ checked }) {
  if (checked) return (
    <svg viewBox="0 0 20 20" fill="currentColor" className="w-4 h-4 shrink-0 text-blue-500 dark:text-blue-400">
      <path fillRule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clipRule="evenodd" />
    </svg>
  );
  return (
    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth={1.5} className="w-4 h-4 shrink-0 text-gray-300 dark:text-zinc-600">
      <circle cx="10" cy="10" r="7.5" />
    </svg>
  );
}

// ── Selector de eventos ──────────────────────────────────────────────────────

function EventSelector({ events, loading, selectedIds, onToggle, onSelectAll, onClearAll }) {
  const count    = selectedIds.size;
  const allCheck = events.length > 0 && count === events.length;

  return (
    <div className="mb-4 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

      {/* Cabecera */}
      <div className="flex items-center justify-between px-4 py-2.5 border-b border-neutral-100 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900/50">
        <span className="text-xs font-semibold tracking-wide uppercase text-gray-500 dark:text-zinc-400">
          Seleccionar eventos
          {events.length > 0 && (
            <span className="ml-2 text-gray-400 dark:text-zinc-500 font-normal normal-case">
              ({events.length} disponibles)
            </span>
          )}
        </span>

        <div className="flex items-center gap-2">
          {count > 0 && (
            <span className="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-full">
              {count} seleccionado{count !== 1 ? 's' : ''}
            </span>
          )}
          <button
            type="button"
            onClick={allCheck ? onClearAll : onSelectAll}
            disabled={events.length === 0}
            className="text-xs text-blue-500 dark:text-blue-400 hover:underline disabled:opacity-40 disabled:cursor-not-allowed"
          >
            {allCheck ? 'Ninguno' : 'Todos'}
          </button>
        </div>
      </div>

      {/* Lista */}
      <div className="overflow-y-auto" style={{ maxHeight: 220 }}>
        {loading ? (
          <div className="flex items-center justify-center gap-2 py-8 text-xs text-gray-400 dark:text-zinc-500">
            <span className="w-4 h-4 border-2 border-gray-300 dark:border-zinc-600 border-t-blue-500 rounded-full animate-spin" />
            Cargando eventos…
          </div>
        ) : events.length === 0 ? (
          <p className="py-8 text-center text-sm text-gray-400 dark:text-zinc-500">
            No hay eventos en el período seleccionado
          </p>
        ) : (
          events.map(ev => {
            const active = selectedIds.has(ev.id);
            return (
              <button
                key={ev.id}
                type="button"
                onClick={() => onToggle(ev.id)}
                className={[
                  'w-full flex items-center gap-3 px-4 py-2.5 text-left transition-colors duration-100',
                  'border-b border-neutral-100 dark:border-neutral-700/60 last:border-b-0',
                  active
                    ? 'bg-blue-50/60 dark:bg-blue-900/20'
                    : 'hover:bg-gray-50 dark:hover:bg-zinc-700/40',
                ].join(' ')}
              >
                <CheckIcon checked={active} />
                <span className="flex-1 min-w-0 text-sm text-gray-800 dark:text-zinc-100 truncate">
                  {ev.title}
                </span>
                <span className="shrink-0 text-xs text-gray-400 dark:text-zinc-500 ml-2">
                  {fmtDate(ev.date)}
                </span>
                <span className="shrink-0 text-xs font-medium text-gray-500 dark:text-zinc-400 bg-gray-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded ml-1">
                  {ev.attendances_count}
                </span>
              </button>
            );
          })
        )}
      </div>
    </div>
  );
}

// ── Sección title ─────────────────────────────────────────────────────────────

function SectionTitle({ children }) {
  return (
    <h2 className="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">
      {children}
    </h2>
  );
}

// ── Empty state (sin eventos seleccionados) ───────────────────────────────────

function NoSelection() {
  return (
    <div className="flex flex-col items-center justify-center py-16 gap-3 text-gray-300 dark:text-zinc-600 select-none">
      <svg className="w-14 h-14" fill="none" stroke="currentColor" strokeWidth={1} viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round"
          d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
      </svg>
      <p className="text-sm font-medium text-gray-400 dark:text-zinc-500">
        Selecciona al menos un evento para ver las comparativas
      </p>
    </div>
  );
}

// ── Hook de datos de comparación ──────────────────────────────────────────────

function useCompareData() {
  const [data, setData]       = useState(null);
  const [loading, setLoading] = useState(false);
  const abortRef              = useRef(null);

  const fetch_ = useCallback(async (eventIds) => {
    if (abortRef.current) abortRef.current.abort();
    if (eventIds.length === 0) { setData(null); return; }

    abortRef.current = new AbortController();
    const { signal } = abortRef.current;
    setLoading(true);

    try {
      const qs  = eventIds.map(id => `eventIds[]=${id}`).join('&');
      const res = await fetch(`/api/statistics/compare/data?${qs}`, { signal });
      setData(await res.json());
    } catch (err) {
      if (err.name !== 'AbortError') setData(null);
    } finally {
      setLoading(false);
    }
  }, []);

  return { data, loading, fetch: fetch_ };
}

// ── Hook de lista de eventos disponibles ──────────────────────────────────────

function useEventsList() {
  const [events, setEvents]   = useState([]);
  const [loading, setLoading] = useState(false);
  const abortRef              = useRef(null);

  const fetch_ = useCallback(async (filters) => {
    if (abortRef.current) abortRef.current.abort();
    abortRef.current = new AbortController();
    const { signal } = abortRef.current;
    setLoading(true);

    try {
      const p = new URLSearchParams();
      if (filters.dateFrom) p.append('dateFrom', filters.dateFrom);
      if (filters.dateTo)   p.append('dateTo',   filters.dateTo);
      const res = await fetch(`/api/statistics/compare/events?${p}`, { signal });
      setEvents(await res.json());
    } catch (err) {
      if (err.name !== 'AbortError') setEvents([]);
    } finally {
      setLoading(false);
    }
  }, []);

  return { events, loading, fetch: fetch_ };
}

// ── Componente principal ──────────────────────────────────────────────────────

export default function ComparaEventosApp() {
  const isDark = useTheme();
  const { filters, updateFilter, clearFilter } = useFilters();

  const { events, loading: evLoading, fetch: fetchEvents } = useEventsList();
  const { data: cmp, loading: cmpLoading, fetch: fetchCompare } = useCompareData();

  const [selectedIds, setSelectedIds] = useState(new Set());

  // ── Cargar lista de eventos cuando cambian los filtros ─────────────────────
  useEffect(() => {
    fetchEvents(filters);
  }, [filters, fetchEvents]);

  // Al cambiar la lista: quitar IDs que ya no existen
  useEffect(() => {
    const validIds = new Set(events.map(e => e.id));
    setSelectedIds(prev => {
      const next = new Set([...prev].filter(id => validIds.has(id)));
      return next.size === prev.size ? prev : next;
    });
  }, [events]);

  // ── Cargar datos de comparación cuando cambia la selección ─────────────────
  useEffect(() => {
    fetchCompare([...selectedIds]);
  }, [selectedIds, fetchCompare]);

  // ── Handlers ──────────────────────────────────────────────────────────────
  const toggle    = id => setSelectedIds(prev => { const n = new Set(prev); n.has(id) ? n.delete(id) : n.add(id); return n; });
  const selectAll = ()  => setSelectedIds(new Set(events.map(e => e.id)));
  const clearAll  = ()  => setSelectedIds(new Set());

  // ── Transformar datos ─────────────────────────────────────────────────────
  const selectedEvents = events.filter(e => selectedIds.has(e.id))
    .sort((a, b) => a.date.localeCompare(b.date));

  const attendancesData = cmp
    ? cmp.attendances
        .filter(d => selectedIds.has(d.id))
        .sort((a, b) => a.date.localeCompare(b.date))
        .map(d => ({ name: d.title, value: d.count }))
    : [];

  const { data: roleData,  categories: roleCats  } = cmp ? pivotDemo(cmp.byRole,  selectedEvents) : { data: [], categories: [] };
  const { data: sexData,   categories: sexCats   } = cmp ? pivotDemo(cmp.bySex,   selectedEvents) : { data: [], categories: [] };
  const { data: groupData, categories: groupCats } = cmp ? pivotDemo(cmp.byGroup, selectedEvents) : { data: [], categories: [] };

  const hasSelection = selectedIds.size > 0;

  return (
    <div>
      {/* ── Filtros de período ── */}
      <FiltersPanel
        filters={filters}
        onUpdate={updateFilter}
        onClear={clearFilter}
        loading={evLoading || cmpLoading}
      />

      {/* ── Selector de eventos ── */}
      <EventSelector
        events={events}
        loading={evLoading}
        selectedIds={selectedIds}
        onToggle={toggle}
        onSelectAll={selectAll}
        onClearAll={clearAll}
      />

      {/* ── Gráficos ── */}
      {!hasSelection ? <NoSelection /> : (
        <>
          {/* Asistencias */}
          <section className="mb-8">
            <SectionTitle>Asistencias por evento</SectionTitle>
            <ChartCard
              title="Total de asistencias"
              description="Número total de asistencias registradas en cada evento seleccionado, ordenados por fecha."
              height={Math.max(260, 60 + selectedIds.size * 44)}
              loading={cmpLoading}
              isEmpty={!cmpLoading && attendancesData.length === 0}
              data={attendancesData}
              isDark={isDark}
              valueLabel="Asistencias"
            >
              <ProgramAttendancesBar data={attendancesData} isDark={isDark} />
            </ChartCard>
          </section>

          {/* Perfil Demográfico */}
          <section className="mb-8">
            <SectionTitle>Perfil Demográfico</SectionTitle>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

              <ChartCard
                title="Por Estamento"
                description="Distribución de asistencias por estamento (Estudiante, Docente, etc.) en los eventos seleccionados."
                height={300}
                loading={cmpLoading}
                isEmpty={!cmpLoading && roleCats.length === 0}
                data={roleData.map(d => ({ name: d.name, value: roleCats.reduce((s, c) => s + (d[c] ?? 0), 0) }))}
                isDark={isDark}
                valueLabel="Asistencias"
                excelData={roleData}
                excelColumns={roleCats}
              >
                <StackedCompareBar data={roleData} categories={roleCats} isDark={isDark} />
              </ChartCard>

              <ChartCard
                title="Por Sexo"
                description="Distribución de asistencias por sexo (Masculino, Femenino, Otro) en los eventos seleccionados."
                height={300}
                loading={cmpLoading}
                isEmpty={!cmpLoading && sexCats.length === 0}
                data={sexData.map(d => ({ name: d.name, value: sexCats.reduce((s, c) => s + (d[c] ?? 0), 0) }))}
                isDark={isDark}
                valueLabel="Asistencias"
                excelData={sexData}
                excelColumns={sexCats}
              >
                <StackedCompareBar data={sexData} categories={sexCats} isDark={isDark} />
              </ChartCard>

              <ChartCard
                title="Por Grupo Priorizado"
                description="Distribución de asistencias por grupo poblacional priorizado en los eventos seleccionados."
                height={300}
                loading={cmpLoading}
                isEmpty={!cmpLoading && groupCats.length === 0}
                data={groupData.map(d => ({ name: d.name, value: groupCats.reduce((s, c) => s + (d[c] ?? 0), 0) }))}
                isDark={isDark}
                valueLabel="Asistencias"
                excelData={groupData}
                excelColumns={groupCats}
              >
                <StackedCompareBar data={groupData} categories={groupCats} isDark={isDark} />
              </ChartCard>

            </div>
          </section>
        </>
      )}
    </div>
  );
}
