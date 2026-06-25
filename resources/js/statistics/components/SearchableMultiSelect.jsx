import React, { useEffect, useMemo, useRef, useState } from 'react';
import { filtrarOpciones } from '../../components/text-filter.js';

const SEARCH_MIN_ITEMS = 3;

function PlusIcon() {
  return (
    <svg className="size-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" aria-hidden="true">
      <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
  );
}

function ChevronIcon({ open }) {
  return (
    <svg
      className={`size-4 shrink-0 text-gray-400 transition-transform ${open ? 'rotate-180' : ''}`}
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      strokeWidth="2"
      aria-hidden="true"
    >
      <path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
    </svg>
  );
}

function RemoveIcon() {
  return (
    <svg className="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5" aria-hidden="true">
      <path strokeLinecap="round" strokeLinejoin="round" d="M6 18 18 6M6 6l12 12" />
    </svg>
  );
}

function normalizeOptions(options) {
  return Object.entries(options ?? {}).map(([value, label]) => ({
    value: String(value),
    label: String(label),
  }));
}

export function SearchableMultiSelect({
  className = '',
  disabled = false,
  helper = null,
  label,
  loading = false,
  onChange,
  options,
  placeholder = 'Selecciona una o más opciones…',
  searchPlaceholder = 'Escribe para buscar…',
  value,
}) {
  const rootRef = useRef(null);
  const searchRef = useRef(null);
  const listRef = useRef(null);
  const [open, setOpen] = useState(false);
  const [search, setSearch] = useState('');
  const [highlighted, setHighlighted] = useState(0);

  const normalizedOptions = useMemo(() => normalizeOptions(options), [options]);
  const selectedValues = useMemo(() => new Set((value ?? []).map(String)), [value]);
  const selectedOptions = useMemo(
    () => (value ?? [])
      .map(current => normalizedOptions.find(option => option.value === String(current)))
      .filter(Boolean),
    [normalizedOptions, value],
  );
  const availableOptions = useMemo(
    () => filtrarOpciones(
      normalizedOptions.filter(option => !selectedValues.has(option.value)),
      search,
    ),
    [normalizedOptions, search, selectedValues],
  );
  const showSearch = normalizedOptions.length > SEARCH_MIN_ITEMS;
  const isDisabled = disabled || loading;

  useEffect(() => {
    if (!open) return undefined;

    const handlePointerDown = event => {
      if (rootRef.current && !rootRef.current.contains(event.target)) {
        setOpen(false);
        setSearch('');
      }
    };

    document.addEventListener('pointerdown', handlePointerDown);

    return () => document.removeEventListener('pointerdown', handlePointerDown);
  }, [open]);

  useEffect(() => {
    if (!open || !showSearch) return;
    searchRef.current?.focus();
  }, [open, showSearch]);

  useEffect(() => {
    setHighlighted(0);
  }, [search, normalizedOptions.length]);

  useEffect(() => {
    const list = listRef.current;
    const element = list?.querySelector(`[data-index="${highlighted}"]`);
    if (!list || !element) return;

    const elementRect = element.getBoundingClientRect();
    const listRect = list.getBoundingClientRect();

    if (elementRect.top < listRect.top) {
      list.scrollTop -= listRect.top - elementRect.top;
    } else if (elementRect.bottom > listRect.bottom) {
      list.scrollTop += elementRect.bottom - listRect.bottom;
    }
  }, [highlighted]);

  const openPanel = () => {
    if (isDisabled) return;
    setOpen(true);
    setSearch('');
    setHighlighted(0);
  };

  const closePanel = () => {
    setOpen(false);
    setSearch('');
  };

  const addOption = option => {
    if (!option || selectedValues.has(option.value)) return;
    onChange([...(value ?? []).map(Number), Number(option.value)]);
    setHighlighted(0);
    setOpen(true);
  };

  const removeOption = optionValue => {
    onChange((value ?? []).filter(current => String(current) !== String(optionValue)));
  };

  const selectHighlighted = () => {
    if (availableOptions.length === 1) {
      addOption(availableOptions[0]);
      return;
    }

    addOption(availableOptions[highlighted]);
  };

  const moveHighlighted = direction => {
    if (!open) {
      openPanel();
      return;
    }

    if (!availableOptions.length) return;
    setHighlighted(current => (current + direction + availableOptions.length) % availableOptions.length);
  };

  const handleKeyDown = event => {
    if (event.key === 'Escape') {
      event.stopPropagation();
      closePanel();
      return;
    }

    if (event.key === 'ArrowDown') {
      event.preventDefault();
      moveHighlighted(1);
      return;
    }

    if (event.key === 'ArrowUp') {
      event.preventDefault();
      moveHighlighted(-1);
      return;
    }

    if (event.key === 'Enter') {
      event.preventDefault();
      if (!open) openPanel();
      else selectHighlighted();
      return;
    }

    if (event.key === 'Backspace' && !search && selectedOptions.length > 0 && event.target === searchRef.current) {
      removeOption(selectedOptions[selectedOptions.length - 1].value);
    }
  };

  return (
    <div ref={rootRef} className={`min-w-[220px] flex-1 ${className}`}>
      <label className="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
        {label}
      </label>

      {selectedOptions.length > 0 && (
        <div className="mb-2 flex min-h-6 flex-wrap gap-1.5">
          {selectedOptions.map(option => (
            <span
              key={option.value}
              className="inline-flex items-center gap-1 rounded-full bg-blue-100 py-0.5 pl-2.5 pr-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"
            >
              <span className="max-w-[12rem] truncate">{option.label}</span>
              <button
                type="button"
                onClick={() => removeOption(option.value)}
                className="inline-flex cursor-pointer items-center justify-center rounded-full p-0.5 transition-colors hover:bg-blue-200 dark:hover:bg-blue-800/60"
                aria-label={`Quitar ${option.label}`}
              >
                <RemoveIcon />
              </button>
            </span>
          ))}
        </div>
      )}

      <div className="relative" onKeyDown={handleKeyDown}>
        <button
          type="button"
          onClick={() => (open ? closePanel() : openPanel())}
          disabled={isDisabled}
          aria-expanded={open}
          className={[
            'flex w-full items-center justify-between gap-2 rounded-lg border px-3 py-2 text-left text-sm transition',
            'border-neutral-200 bg-white text-gray-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white',
            'focus:outline-none focus:ring-2 focus:ring-blue-500',
            open ? 'border-blue-500 ring-2 ring-blue-500' : '',
            isDisabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
          ].join(' ')}
        >
          <span className="truncate text-gray-400">{loading ? 'Cargando opciones…' : placeholder}</span>
          <ChevronIcon open={open} />
        </button>

        {open && (
          <div className="absolute z-[70] mt-1 w-full rounded-lg border border-neutral-200 bg-white shadow-lg dark:border-zinc-600 dark:bg-zinc-800">
            {showSearch && (
              <div className="border-b border-neutral-100 p-2 dark:border-zinc-700">
                <input
                  ref={searchRef}
                  type="text"
                  value={search}
                  onChange={event => setSearch(event.target.value)}
                  placeholder={searchPlaceholder}
                  autoComplete="off"
                  className="w-full rounded-md border border-neutral-200 bg-white px-2.5 py-1.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                />
              </div>
            )}

            <ul ref={listRef} className="max-h-52 overflow-y-auto py-1 text-sm">
              {availableOptions.map((option, index) => (
                <li key={option.value} data-index={index}>
                  <button
                    type="button"
                    onMouseEnter={() => setHighlighted(index)}
                    onClick={() => addOption(option)}
                    className={[
                      'flex w-full cursor-pointer items-center gap-2 px-3 py-2 text-left text-gray-800 transition-colors hover:bg-blue-50 dark:text-gray-200 dark:hover:bg-zinc-700',
                      highlighted === index ? 'bg-blue-50 dark:bg-zinc-700' : '',
                    ].join(' ')}
                  >
                    <PlusIcon />
                    <span className="truncate">{option.label}</span>
                  </button>
                </li>
              ))}

              {availableOptions.length === 0 && (
                <li className="px-3 py-3 text-center text-gray-400 dark:text-zinc-500">
                  {search ? 'Sin resultados' : 'No quedan opciones por agregar'}
                </li>
              )}
            </ul>
          </div>
        )}
      </div>

      {helper && (
        <p className="mt-1 text-[11px] text-gray-400 dark:text-zinc-500">{helper}</p>
      )}
    </div>
  );
}
