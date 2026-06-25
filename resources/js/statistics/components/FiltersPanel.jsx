import React, { useEffect, useState } from 'react';
import { SearchableMultiSelect } from './SearchableMultiSelect.jsx';
import { buildStatisticsQuery } from '../utils/query.js';

const XIcon = () => (
  <svg viewBox="0 0 20 20" fill="currentColor" className="w-2.5 h-2.5" aria-hidden="true">
    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
  </svg>
);

function DateField({ label, value, onChange, onClear }) {
  return (
    <div className="min-w-0">
      <label className="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
        {label}
      </label>
      <div className="flex items-center gap-1.5">
        <input
          type="date"
          value={value}
          onChange={event => onChange(event.target.value)}
          className={[
            'min-w-0 flex-1 rounded-lg px-3 py-2 text-sm',
            'bg-white dark:bg-zinc-800',
            'border border-neutral-200 dark:border-neutral-700',
            'text-gray-900 dark:text-gray-100',
            'focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400',
            'transition-colors duration-150',
          ].join(' ')}
        />
        <button
          type="button"
          onClick={onClear}
          aria-label={`Limpiar ${label.toLowerCase()}`}
          style={{ visibility: value ? 'visible' : 'hidden' }}
          className="flex size-5 shrink-0 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-red-100 hover:text-red-500 dark:bg-zinc-600 dark:text-gray-300 dark:hover:bg-red-900/40 dark:hover:text-red-400"
        >
          <XIcon />
        </button>
      </div>
    </div>
  );
}

function TogglePill({ checked, children, disabled = false, onChange }) {
  return (
    <label
      className={[
        'inline-flex cursor-pointer items-center gap-2 rounded-full border px-3 py-2 text-sm transition',
        'border-neutral-200 bg-neutral-50 text-gray-700 hover:border-blue-300 hover:bg-blue-50',
        'dark:border-zinc-700 dark:bg-zinc-800/70 dark:text-gray-200 dark:hover:border-blue-700 dark:hover:bg-blue-950/30',
        checked ? 'border-blue-300 bg-blue-50 text-blue-700 dark:border-blue-700 dark:bg-blue-950/40 dark:text-blue-300' : '',
        disabled ? 'cursor-not-allowed opacity-60' : '',
      ].join(' ')}
    >
      <input
        type="checkbox"
        checked={checked}
        disabled={disabled}
        onChange={event => onChange(event.target.checked)}
        className="rounded border-neutral-300 text-blue-600 focus:ring-blue-500"
      />
      {children}
    </label>
  );
}

export function FiltersPanel({ filters, onUpdate, onClear, loading, actions = null }) {
  const [options, setOptions] = useState({ showCampuses: false, campuses: {}, dependencies: {} });
  const [optionsLoading, setOptionsLoading] = useState(true);
  const [optionsError, setOptionsError] = useState(false);

  useEffect(() => {
    const controller = new AbortController();

    async function loadOptions() {
      setOptionsLoading(true);
      setOptionsError(false);
      try {
        const qs = buildStatisticsQuery(filters);
        const response = await fetch(`/api/statistics/filter-options${qs ? `?${qs}` : ''}`, {
          signal: controller.signal,
        });
        const data = await response.json();

        if (!controller.signal.aborted) {
          setOptions(data);
        }
      } catch (error) {
        if (error.name !== 'AbortError') {
          setOptions({ showCampuses: false, campuses: {}, dependencies: {} });
          setOptionsError(true);
        }
      } finally {
        if (!controller.signal.aborted) setOptionsLoading(false);
      }
    }

    loadOptions();

    return () => controller.abort();
  }, [filters, onUpdate]);

  const selectedCampusCount = filters.campusIds?.length ?? 0;
  const selectedDependencyCount = filters.dependencyIds?.length ?? 0;
  const activeFilters = [
    options.showCampuses && (filters.allCampuses ? 'todas las sedes' : `${selectedCampusCount} sede(s)`),
    selectedDependencyCount > 0 && `${selectedDependencyCount} dependencia(s)`,
    filters.onlyOwnEvents && 'solo mis eventos',
  ].filter(Boolean);
  const filtersGridClassName = options.showCampuses
    ? 'grid gap-4 xl:grid-cols-[minmax(260px,1fr)_minmax(260px,1fr)_minmax(320px,0.95fr)]'
    : 'grid gap-4 lg:grid-cols-[minmax(260px,1fr)_minmax(320px,0.95fr)]';

  return (
    <div className="mb-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
      <div className={filtersGridClassName}>
        {options.showCampuses && (
          <div className="rounded-xl border border-neutral-100 bg-neutral-50/60 p-3 dark:border-zinc-800 dark:bg-zinc-950/30">
            <SearchableMultiSelect
              className="min-w-0"
              label={`Sedes ${selectedCampusCount > 0 ? `(${selectedCampusCount})` : ''}`}
              options={options.campuses}
              value={filters.campusIds ?? []}
              disabled={filters.allCampuses}
              loading={optionsLoading}
              placeholder="Agregar sedes…"
              searchPlaceholder="Escribe para buscar sedes…"
              onChange={values => {
                onUpdate('campusIds', values);
                onUpdate('allCampuses', false);
                onUpdate('dependencyIds', []);
              }}
            />
          </div>
        )}

        <div className="rounded-xl border border-neutral-100 bg-neutral-50/60 p-3 dark:border-zinc-800 dark:bg-zinc-950/30">
          <SearchableMultiSelect
            className="min-w-0"
            label={`Dependencias ${selectedDependencyCount > 0 ? `(${selectedDependencyCount})` : ''}`}
            options={options.dependencies}
            value={filters.dependencyIds ?? []}
            loading={optionsLoading}
            placeholder="Agregar dependencias…"
            searchPlaceholder="Escribe para buscar dependencias…"
            helper="Sin selección: todas las permitidas."
            onChange={values => onUpdate('dependencyIds', values)}
          />
        </div>

        <div className="rounded-xl border border-neutral-100 bg-neutral-50/60 p-3 dark:border-zinc-800 dark:bg-zinc-950/30">
          <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
            <DateField
              label="Fecha desde"
              value={filters.dateFrom}
              onChange={value => onUpdate('dateFrom', value)}
              onClear={() => onClear('dateFrom')}
            />

            <DateField
              label="Fecha hasta"
              value={filters.dateTo}
              onChange={value => onUpdate('dateTo', value)}
              onClear={() => onClear('dateTo')}
            />
          </div>
        </div>
      </div>

      <div className="mt-4 flex flex-col gap-3 border-t border-neutral-100 pt-3 dark:border-zinc-800 lg:flex-row lg:items-center lg:justify-between">
        <div className="flex flex-wrap items-center gap-2">
          {options.showCampuses && (
            <TogglePill
              checked={Boolean(filters.allCampuses)}
              onChange={checked => {
                onUpdate('allCampuses', checked);
                if (checked) {
                  onUpdate('campusIds', []);
                  onUpdate('dependencyIds', []);
                }
              }}
            >
              Todas las sedes
            </TogglePill>
          )}

          <TogglePill
            checked={Boolean(filters.onlyOwnEvents)}
            onChange={checked => onUpdate('onlyOwnEvents', checked)}
          >
            Solo mis eventos
          </TogglePill>
        </div>

        <div className="flex flex-wrap items-center gap-3">
          {actions && <div>{actions}</div>}

          <div
            className="flex items-center gap-1.5 text-xs text-gray-400 transition-opacity duration-200 dark:text-zinc-500"
            style={{ opacity: loading ? 1 : 0, pointerEvents: 'none' }}
          >
            <span className="size-3 shrink-0 animate-spin rounded-full border-2 border-gray-300 border-t-blue-500 dark:border-zinc-600" />
            Cargando…
          </div>
        </div>
      </div>

      <div className="mt-3 flex flex-wrap items-center gap-2 text-xs">
        <span className="text-gray-400 dark:text-zinc-500">Filtros aplicados:</span>
        {activeFilters.length ? activeFilters.map(filter => (
          <span
            key={filter}
            className="rounded-full bg-blue-50 px-2.5 py-1 font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300"
          >
            {filter}
          </span>
        )) : (
          <span className="text-gray-600 dark:text-gray-300">ninguno adicional</span>
        )}
        {optionsError && (
          <span className="text-red-600 dark:text-red-400">
            No se pudieron actualizar las opciones de filtro.
          </span>
        )}
      </div>
    </div>
  );
}
