import { ChevronLeftIcon, ChevronRightIcon } from '../icons.jsx';

function windowed(current, last) {
    const pages = [];
    let prev = 0;
    for (let p = 1; p <= last; p++) {
        if (p === 1 || p === last || Math.abs(p - current) <= 1) {
            if (prev && p - prev > 1) pages.push('…');
            pages.push(p);
            prev = p;
        }
    }
    return pages;
}

/** Paginación sutil (botones tipo ghost), equivalente a la del listado Livewire. */
export default function Pagination({ currentPage, lastPage, onPageChange }) {
    if (lastPage <= 1) return null;

    const ghost = 'inline-flex items-center justify-center size-7 rounded-md text-gray-400 dark:text-zinc-500 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300 disabled:opacity-30 disabled:pointer-events-none transition-colors';

    return (
        <nav className="flex items-center gap-0.5" aria-label="Paginación">
            <button type="button" onClick={() => onPageChange(currentPage - 1)} disabled={currentPage <= 1}
                className={ghost} aria-label="Página anterior">
                <ChevronLeftIcon className="size-4" />
            </button>

            {windowed(currentPage, lastPage).map((page, i) =>
                page === '…' ? (
                    <span key={`gap-${i}`} className="px-1 text-xs text-gray-400 dark:text-zinc-600 select-none">…</span>
                ) : (
                    <button key={page} type="button" onClick={() => onPageChange(page)}
                        aria-current={page === currentPage ? 'page' : undefined}
                        className={`inline-flex items-center justify-center min-w-7 h-7 px-1.5 rounded-md text-xs transition-colors ${page === currentPage
                            ? 'font-semibold text-blue-600 bg-blue-50 dark:text-blue-300 dark:bg-blue-900/30'
                            : 'font-medium text-gray-500 dark:text-zinc-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200'}`}>
                        {page}
                    </button>
                )
            )}

            <button type="button" onClick={() => onPageChange(currentPage + 1)} disabled={currentPage >= lastPage}
                className={ghost} aria-label="Página siguiente">
                <ChevronRightIcon className="size-4" />
            </button>
        </nav>
    );
}
