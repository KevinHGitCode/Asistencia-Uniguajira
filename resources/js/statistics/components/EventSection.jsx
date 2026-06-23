import React, { useState } from "react";
import EventCard from "./EventCard.jsx";

const PER_SECTION = 9;

export default function EventSection({ title, icon, events, emptyMessage, emptyHint }) {
    const [expanded, setExpanded] = useState(false);

    // Estado vacío: fila compacta (título pequeño + mensaje al lado), poca altura.
    if (events.length === 0) {
        return (
            <div className="relative flex w-full flex-wrap items-center gap-x-2 gap-y-0.5 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 px-5 py-3">
                <h2 className="flex items-center gap-1.5 text-base font-semibold text-gray-600 dark:text-gray-300 [&>svg]:!h-5 [&>svg]:!w-5">
                    {icon}
                    {title}
                </h2>
                <span className="text-sm text-gray-400 dark:text-gray-500">
                    — {emptyMessage}
                    {emptyHint ? <span className="hidden sm:inline"> · {emptyHint}</span> : null}
                </span>
            </div>
        );
    }

    const visible = expanded ? events : events.slice(0, PER_SECTION);
    const hasMore = events.length > PER_SECTION;

    return (
        <div className="relative flex w-full flex-1 flex-col gap-4 p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
            {/* Header */}
            <div className="flex items-center justify-between mb-4">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    {icon}
                    {title}
                </h2>
                <span className="px-3 py-1 text-sm font-medium bg-[#e2a542] text-white rounded-2xl">
                    {events.length} {events.length === 1 ? "evento" : "eventos"}
                </span>
            </div>

            {/* Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {visible.map((event) => (
                    <EventCard key={event.id} event={event} />
                ))}
            </div>

            {/* Ver más / Ver menos */}
            {hasMore && (
                <div className="flex justify-center mt-2">
                    <button
                        onClick={() => setExpanded(!expanded)}
                        className="cursor-pointer px-4 py-2 text-sm font-medium text-[#e2a542] hover:text-[#c8912e] border border-[#e2a542] hover:bg-[#e2a542]/10 rounded-xl transition"
                    >
                        {expanded
                            ? `Ver menos`
                            : `Ver todos (${events.length - PER_SECTION} más)`
                        }
                    </button>
                </div>
            )}
        </div>
    );
}