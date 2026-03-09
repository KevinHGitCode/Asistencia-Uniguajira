import React from 'react';
import { ChevronLeftIcon, ChevronRightIcon } from './AdminEventosIcons.jsx';

const btnBase =
    "flex items-center justify-center w-9 h-9 text-sm rounded-lg transition-all duration-150 focus:outline-none";
const btnActive =
    "bg-[#e2a542] text-white font-semibold shadow-sm";
const btnInactive =
    "bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-300 border border-neutral-200 dark:border-neutral-700 hover:border-[#e2a542] hover:text-[#e2a542]";
const btnDisabled =
    "bg-gray-100 dark:bg-zinc-800 text-gray-300 dark:text-gray-600 border border-neutral-200 dark:border-neutral-700 cursor-not-allowed";

function getPages(currentPage, totalPages) {
    const pages = [];

    if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) pages.push(i);
        return pages;
    }

    pages.push(1);

    let start = Math.max(2, currentPage - 1);
    let end = Math.min(totalPages - 1, currentPage + 1);

    if (currentPage <= 3) { start = 2; end = 4; }
    else if (currentPage >= totalPages - 2) { start = totalPages - 3; end = totalPages - 1; }

    if (start > 2) pages.push("…left");
    for (let i = start; i <= end; i++) pages.push(i);
    if (end < totalPages - 1) pages.push("…right");

    pages.push(totalPages);
    return pages;
}

export default function Pagination({ currentPage, totalPages, onPageChange }) {
    if (totalPages <= 1) return null;

    const pages = getPages(currentPage, totalPages);

    return (
        <div className="flex items-center justify-center gap-1.5 mt-6 flex-wrap">
            <button
                onClick={() => onPageChange(currentPage - 1)}
                disabled={currentPage === 1}
                className={`${btnBase} ${currentPage === 1 ? btnDisabled : btnInactive}`}
                title="Anterior"
            >
                <ChevronLeftIcon />
            </button>

            {pages.map((page) =>
                typeof page === "string" ? (
                    <span key={page} className="w-9 h-9 flex items-center justify-center text-sm text-gray-400">…</span>
                ) : (
                    <button
                        key={page}
                        onClick={() => onPageChange(page)}
                        className={`${btnBase} ${page === currentPage ? btnActive : btnInactive}`}
                    >
                        {page}
                    </button>
                )
            )}

            <button
                onClick={() => onPageChange(currentPage + 1)}
                disabled={currentPage === totalPages}
                className={`${btnBase} ${currentPage === totalPages ? btnDisabled : btnInactive}`}
                title="Siguiente"
            >
                <ChevronRightIcon />
            </button>
        </div>
    );
}