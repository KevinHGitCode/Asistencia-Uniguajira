import React, { useState } from "react";
import EventCard from "./EventCard.jsx";

const PER_SECTION = 9;

export default function EventSection({ title, icon, events, emptyMessage, emptyHint }) {
    const [expanded, setExpanded] = useState(false);

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

            {/* Vacío */}
            {events.length === 0 && (
                <div className="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg className="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" strokeWidth={2} viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p className="text-lg font-medium">{emptyMessage}</p>
                    <p className="text-sm mt-1">{emptyHint}</p>
                </div>
            )}

            {/* Cards */}
            {events.length > 0 && (
                <>
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
                </>
            )}
        </div>
    );
}