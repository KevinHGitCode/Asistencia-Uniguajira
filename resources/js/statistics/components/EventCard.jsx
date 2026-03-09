import React from 'react';
import { CalendarIcon, ClockIcon, LocationIcon, UserIcon } from './AdminEventosIcons.jsx';
import { formatDate, formatTime } from '../utils/formatters.js';

export function EventCardSkeleton() {
    return (
        <div className="block p-4 rounded-2xl border border-neutral-200 dark:border-neutral-600 bg-white dark:bg-zinc-800 animate-pulse">
            <div className="h-5 bg-gray-200 dark:bg-zinc-700 rounded w-3/4 mb-3" />
            <div className="h-3 bg-gray-200 dark:bg-zinc-700 rounded w-1/2 mb-2" />
            <div className="space-y-2">
                <div className="h-3 bg-gray-200 dark:bg-zinc-700 rounded w-1/3" />
                <div className="h-3 bg-gray-200 dark:bg-zinc-700 rounded w-1/4" />
            </div>
            <div className="flex gap-2 mt-3">
                <div className="h-5 bg-gray-200 dark:bg-zinc-700 rounded w-20" />
                <div className="h-5 bg-gray-200 dark:bg-zinc-700 rounded w-16" />
            </div>
        </div>
    );
}

export default function EventCard({ event }) {
    return (
        <a
            href={`/eventos/${event.id}`}
            className="block p-4 rounded-2xl border border-neutral-200 dark:border-neutral-600 hover:border-[#e2a542] hover:shadow-lg transition-all duration-200 bg-white dark:bg-zinc-800"
        >
            <div className="flex items-start justify-between mb-2">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1">
                    {event.title}
                </h3>
            </div>

            {event.user_name && (
                <div className="flex items-center gap-1.5 mb-2 text-xs text-gray-500 dark:text-gray-400">
                    <UserIcon />
                    <span>Creado por: {event.user_name}</span>
                </div>
            )}

            <div className="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                {event.date && (
                    <div className="flex items-center gap-2">
                        <CalendarIcon />
                        <span>{formatDate(event.date)}</span>
                    </div>
                )}
                {event.start_time && (
                    <div className="flex items-center gap-2">
                        <ClockIcon />
                        <span>{formatTime(event.start_time)}</span>
                    </div>
                )}
                {event.location && (
                    <div className="flex items-center gap-2">
                        <LocationIcon />
                        <span className="line-clamp-1">{event.location}</span>
                    </div>
                )}
            </div>

            {event.dependency_name && (
                <div className="flex items-center flex-wrap text-xs mt-2 gap-2">
                    <span className="px-2 py-1 bg-[#cc5e50] text-white rounded">
                        {event.dependency_name}
                    </span>
                    {event.area_name && (
                        <span className="px-2 py-1 bg-[#62a9b6] text-white rounded">
                            {event.area_name}
                        </span>
                    )}
                </div>
            )}

            {event.description && (
                <p className="mt-3 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                    {event.description}
                </p>
            )}
        </a>
    );
}