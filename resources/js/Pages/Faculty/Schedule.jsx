import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ScrollToTop from '@/Components/ScrollToTop';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';



const DAY_COLORS = {
    Mon: 'from-blue-500 to-blue-600',
    Tue: 'from-violet-500 to-violet-600',
    Wed: 'from-emerald-500 to-emerald-600',
    Thu: 'from-amber-500 to-amber-600',
    Fri: 'from-rose-500 to-rose-600',
    Sat: 'from-cyan-500 to-cyan-600',
    Sun: 'from-gray-400 to-gray-500',
};

const SYNC_STYLES = {
    synced: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/30',
    pending: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/30',
    failed: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/30',
};

const ScheduleCard = ({ item, dayShort, isInternalView = false }) => {
    const isInternal = item.type === 'internal' || item.syncStatus !== undefined;

    return (
        <div className={`group relative flex flex-col rounded-2xl border p-4 shadow-sm transition-all duration-200 
            ${isInternal
                ? 'bg-blue-50/10 dark:bg-blue-900/10 border-blue-200/50 dark:border-blue-800/40'
                : 'border-gray-200/80 dark:border-gray-700/80 bg-white dark:bg-gray-800'
            } hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 hover:scale-[1.01]`}>

            {/* Header: Badges */}
            <div className="flex justify-between items-start mb-4 gap-2">
                <div className="flex flex-wrap items-center gap-2">
                    {/* Day Indicator Dot */}
                    <div className={`h-2.5 w-2.5 shrink-0 rounded-full bg-gradient-to-r ${DAY_COLORS[dayShort] ?? 'from-gray-400 to-gray-500'} shadow-sm`} />

                    {/* Type Badge */}
                    <span className={`shrink-0 rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset 
                        ${isInternal
                            ? 'bg-amber-100 text-amber-700 ring-amber-600/20 dark:bg-amber-900/40 dark:text-amber-300'
                            : 'bg-emerald-100 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/40 dark:text-emerald-400'
                        }`}>
                        {isInternal ? 'Internal' : 'Official'}
                    </span>

                    {/* Operational Status (only in Internal tab) */}
                    {isInternalView && isInternal && item.isOperational !== undefined && (
                        <span className={`inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase ring-1 ring-inset 
                            ${item.isOperational
                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400'
                                : 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400'
                            }`}>
                            {item.isOperational ? 'Operational' : 'Non-Operational'}
                        </span>
                    )}
                </div>

                {/* Sync Status (only in Internal tab) */}
                {isInternalView && isInternal && item.syncStatus && (
                    <span className={`inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset ${SYNC_STYLES[item.syncStatus?.toLowerCase()] ?? SYNC_STYLES.pending}`}>
                        {item.syncStatus}
                    </span>
                )}
            </div>

            {/* Subject & Details */}
            <div className="mb-4">
                <div className="flex items-start justify-between gap-2">
                    <h4 className="font-extrabold text-gray-900 dark:text-white leading-tight break-words text-sm sm:text-base">
                        {item.subject || (isInternal ? 'Operational Duty' : 'Untitled Subject')}
                    </h4>
                    {item.isChanged && isInternalView && (
                        <span className="shrink-0 inline-flex items-center rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400" title={`Moved from ${item.originalDay}`}>
                            Changed
                        </span>
                    )}
                </div>
                {/* Subject Code & Room */}
                <p className="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                    <span>{item.code}</span>
                    {item.room && item.room !== 'TBA' && (
                        <>
                            <span>•</span>
                            <span className="font-semibold">{item.room}</span>
                        </>
                    )}
                </p>
                {/* Program & Section */}
                {(item.programCode || item.yearLevel || item.sectionName) && (
                    <p className="mt-1.5 text-xs font-bold text-amber-600 dark:text-amber-500">
                        {[item.programCode, (item.yearLevel || item.sectionName) ? [item.yearLevel, item.sectionName].filter(Boolean).join('-') : null].filter(Boolean).join(' ')}
                    </p>
                )}
            </div>

            {/* Time Section (Unified) */}
            <div className="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700/50">
                <div className="flex items-center justify-between mb-3">
                    <div className="flex items-center gap-2">
                        <div className="p-1.5 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                            <svg className="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div className="flex items-center gap-1.5 text-sm font-extrabold text-gray-900 dark:text-white">
                            <span>{item.startTime}</span>
                            <span className="text-gray-300 dark:text-gray-600">→</span>
                            <span>{item.endTime}</span>
                        </div>
                    </div>
                </div>

                {/* Footer: Metadata */}
                <div className="flex items-center justify-between pt-3 border-t border-gray-50 dark:border-gray-700/50 border-dashed">
                    <div className="flex flex-col gap-0.5">
                        <div className="flex items-center gap-2">
                            <span className="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tight">
                                {item.hours || item.requiredHours} HR{(item.hours || item.requiredHours) !== 1 ? 'S' : ''}
                            </span>
                            {item.scheduleCode && (
                                <>
                                    <span className="h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-600" />
                                    <span className="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tight">
                                        {item.scheduleCode}
                                    </span>
                                </>
                            )}
                        </div>
                        {item.effectiveFrom && (
                            <span className="text-[10px] font-medium text-gray-400 dark:text-gray-600 tabular-nums">
                                {item.effectiveFrom} — {item.effectiveUntil || 'Present'}
                            </span>
                        )}
                    </div>

                    {isInternal && item.syncedAt && (
                        <div className="group/sync relative">
                            <svg className="h-3.5 w-3.5 text-emerald-500 cursor-help" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <div className="absolute bottom-full right-0 mb-2 invisible group-hover/sync:visible w-32 px-2 py-1 bg-gray-900 text-white text-[10px] rounded shadow-xl z-10 text-center">
                                Synced: {item.syncedAt}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};


export default function Schedule({ weeklySchedule, internalSchedule, facultyName }) {
    const [activeTab, setActiveTab] = useState('overall');

    const toMin = (t) => {
        if (!t || t === '--:--') return 9999;
        const [time, period] = t.split(' ');
        if (!time || !period) return 9999;
        let [h, m] = time.split(':').map(Number);
        if (period === 'PM' && h !== 12) h += 12;
        if (period === 'AM' && h === 12) h = 0;
        return h * 60 + m;
    };

    // Sort classes within each day by startTime chronologically
    const sortedSchedule = weeklySchedule.map((dayData) => ({
        ...dayData,
        classes: [...dayData.classes].sort((a, b) => toMin(a.startTime) - toMin(b.startTime)),
    }));

    // Gather all changed official class IDs from the internal schedule across all days
    const changedOfficialClassIds = new Set(
        internalSchedule
            .flatMap(d => d.entries)
            .filter(e => e.isChanged && e.originalScheduleDetailId)
            .map(e => e.originalScheduleDetailId)
    );

    // Combine both schedules
    const daysArr = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const combinedSchedule = daysArr.map(day => {
        const officialDay = sortedSchedule.find(d => d.day === day) || { classes: [] };
        const internalDay = internalSchedule.find(d => d.day === day) || { entries: [] };

        const combinedItems = [
            ...officialDay.classes
                .filter(c => !changedOfficialClassIds.has(c.id))
                .map(c => ({ ...c, type: 'official' })),
            ...internalDay.entries
                .filter(e => e.isChanged)
                .map(e => ({ ...e, type: 'internal' }))
        ].sort((a, b) => toMin(a.startTime) - toMin(b.startTime));

        return {
            day,
            shortDay: day.substring(0, 3),
            items: combinedItems
        };
    }).filter(d => d.items.length > 0);

    // Count total weekly hours // based on official schedule
    const totalWeeklyHours = sortedSchedule.reduce(
        (sum, day) => sum + day.classes.reduce((s, c) => s + c.hours, 0),
        0,
    );

    // Filter internal schedule: only show entries that are approved OR have request history
    // And only show the latest approved entry per course and section to avoid duplication
    const filteredInternalSchedule = internalSchedule.map(dayData => {
        const filteredEntries = dayData.entries.filter(entry => {
            return entry.isApproved || entry.hasRequestHistory;
        });

        // Group by course code + section to find latest approved per subject
        const latestApprovedMap = new Map();
        filteredEntries.forEach(entry => {
            if (entry.isApproved && entry.code) {
                const key = `${entry.code}-${entry.sectionName || ''}`;
                const existing = latestApprovedMap.get(key);
                if (!existing || entry.id > existing.id) {
                    latestApprovedMap.set(key, entry);
                }
            }
        });

        const latestApprovedIds = new Set(Array.from(latestApprovedMap.values()).map(e => e.id));

        const finalEntries = filteredEntries.filter(entry => {
            if (entry.isApproved) {
                return latestApprovedIds.has(entry.id);
            }
            return true;
        });

        return {
            ...dayData,
            entries: finalEntries
        };
    }).filter(dayData => dayData.entries.length > 0);

    return (
        <AuthenticatedLayout>
            <Head title="Schedule & Attendance" />

            {/* ── Header ─────────────────────────────── */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <Link
                        href={route('faculty.dashboard')}
                        className="inline-flex items-center gap-1 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors mb-2"
                    >
                        <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                        Back to Dashboard
                    </Link>
                    <h1 className="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                        Schedule & Attendance
                    </h1>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {facultyName ? `${facultyName}'s` : 'Your'} weekly teaching schedule and attendance history.
                    </p>
                </div>

                {/* Summary */}
                <div className="flex gap-3">
                    <div className="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 px-5 py-3 shadow-sm text-center">
                        <p className="text-xs font-medium text-gray-500 dark:text-gray-400">Teaching Days</p>
                        <p className="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{sortedSchedule.length}</p>
                    </div>
                    <div className="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 px-5 py-3 shadow-sm text-center">
                        <p className="text-xs font-medium text-gray-500 dark:text-gray-400">Weekly Hours</p>
                        <p className="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{totalWeeklyHours}</p>
                    </div>
                </div>
            </div>

            {/* ── Tab Toggle ──────────────────────────── */}
            <div className="flex gap-2 mb-6 overflow-x-auto pb-2 sm:pb-0">
                <button
                    onClick={() => setActiveTab('overall')}
                    className={`shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 ${activeTab === 'overall'
                        ? 'bg-[#7a1315] text-white shadow-sm'
                        : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'
                        }`}
                >
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 0H4.5m-1.5 6h18m-18 6h18" />
                    </svg>
                    Overall Schedule
                </button>
                <button
                    onClick={() => setActiveTab('internal')}
                    className={`shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 ${activeTab === 'internal'
                        ? 'bg-[#7a1315] text-white shadow-sm'
                        : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'
                        }`}
                >
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Internal Schedule
                </button>
                <button
                    onClick={() => setActiveTab('official')}
                    className={`shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 ${activeTab === 'official'
                        ? 'bg-[#7a1315] text-white shadow-sm'
                        : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'
                        }`}
                >
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                    </svg>
                    Official Schedule
                </button>
            </div>

            {/* ── Overall Schedule View ──────────────── */}
            {activeTab === 'overall' && (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 items-start">
                    {combinedSchedule.length > 0 ? (
                        combinedSchedule.map((dayData) => (
                            <div
                                key={dayData.day}
                                className="flex flex-col h-full rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 shadow-sm overflow-hidden"
                            >
                                {/* Day header */}
                                <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/30 dark:bg-gray-800/50">
                                    <div className="flex items-center gap-4">
                                        <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${DAY_COLORS[dayData.shortDay] ?? 'from-gray-400 to-gray-500'} text-white font-bold text-sm shadow-sm`}>
                                            {dayData.shortDay}
                                        </div>
                                        <div>
                                            <h3 className="text-base font-bold text-gray-900 dark:text-white">{dayData.day}</h3>
                                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                                {dayData.items.length} {dayData.items.length === 1 ? 'item' : 'items'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div className="p-4 bg-gray-50/10 dark:bg-gray-800/20 flex-1 flex flex-col gap-4">
                                    {dayData.items.map((item) => (
                                        <ScheduleCard key={`${item.type}-${item.id}`} item={item} dayShort={dayData.shortDay} />
                                    ))}
                                </div>

                                {/* Day Footer */}
                                <div className="px-5 py-3 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/80 text-right">
                                    <span className="inline-flex items-center rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-500/10 dark:bg-zinc-400/10 dark:text-zinc-400 dark:ring-zinc-400/30">
                                        Total: {dayData.items.reduce((s, item) => s + (item.hours || item.requiredHours || 0), 0)} {dayData.items.reduce((s, item) => s + (item.hours || item.requiredHours || 0), 0) === 1 ? 'hour' : 'hours'}
                                    </span>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="col-span-full flex flex-col items-center justify-center rounded-3xl border border-dashed border-gray-300 dark:border-gray-700 py-20 text-center bg-white dark:bg-gray-800/80">
                            <div className="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 mb-4">
                                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <p className="text-sm font-semibold text-gray-600 dark:text-gray-300">No overall schedule found</p>
                            <p className="mt-1 text-xs text-gray-400 dark:text-gray-500">You have no official or internal classes yet.</p>
                        </div>
                    )}
                </div>
            )}

            {/* ── Official Schedule View ──────────────── */}
            {activeTab === 'official' && (
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 items-start">
                                        {sortedSchedule.length > 0 ? (
                                            sortedSchedule.map((dayData) => (
                                                <div
                                                    key={dayData.day}
                                                    className="flex flex-col h-full rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 shadow-sm overflow-hidden"
                                                >
                                                    {/* Day header */}
                                                    <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/30 dark:bg-gray-800/50">
                                                        <div className="flex items-center gap-4">
                                                            <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${DAY_COLORS[dayData.shortDay] ?? 'from-gray-400 to-gray-500'} text-white font-bold text-sm shadow-sm`}>
                                                                {dayData.shortDay}
                                                            </div>
                                                            <div>
                                                                <h3 className="text-base font-bold text-gray-900 dark:text-white">{dayData.day}</h3>
                                                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                                                    {dayData.classes.length} {dayData.classes.length === 1 ? 'class' : 'classes'}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                        <div className="p-4 bg-gray-50/10 dark:bg-gray-800/20 flex-1 flex flex-col gap-4">
                                            {dayData.classes.map((cls) => (
                                                <ScheduleCard key={cls.id} item={cls} dayShort={dayData.shortDay} />
                                            ))}
                                        </div>

                                        {/* Day Footer */}
                                        <div className="px-5 py-3 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/80 text-right">
                                            <span className="inline-flex items-center rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-500/10 dark:bg-zinc-400/10 dark:text-zinc-400 dark:ring-zinc-400/30">
                                                Total: {dayData.classes.reduce((s, c) => s + (c.hours || 0), 0)} {dayData.classes.reduce((s, c) => s + (c.hours || 0), 0) === 1 ? 'hour' : 'hours'}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="col-span-full flex flex-col items-center justify-center rounded-3xl border border-dashed border-gray-300 dark:border-gray-700 py-20 text-center bg-white dark:bg-gray-800/80">
                                                <div className="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 mb-4">
                                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                                                        <path strokeLinecap="round" strokeLinejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                                    </svg>
                                                </div>
                                                <p className="text-sm font-semibold text-gray-600 dark:text-gray-300">No active schedule found</p>
                                                <p className="mt-1 text-xs text-gray-400 dark:text-gray-500">Contact your department to set up your teaching schedule.</p>
                                            </div>
                                        )}
                                    </div>
                                )}

            {/* ── Internal Schedule View ──────────────── */}
            {activeTab === 'internal' && (
                <>
                    {/* Internal schedule summary */}
                    {filteredInternalSchedule.length > 0 && (
                        <div className="mb-6 rounded-2xl border border-amber-200/60 dark:border-amber-800/40 bg-amber-50/50 dark:bg-amber-900/10 p-4">
                            <div className="flex items-start gap-3">
                                <svg className="h-5 w-5 text-amber-500 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                </svg>
                                <div>
                                    <p className="text-sm font-bold text-amber-800 dark:text-amber-300">Internal Schedule</p>
                                    <p className="mt-0.5 text-xs text-amber-600 dark:text-amber-400">
                                        This reflects your current active schedule and serves as the primary basis for attendance tracking, payroll, and biometric validation. This may include approved modifications from the official teaching load.
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 items-start">
                        {filteredInternalSchedule.length > 0 ? (
                            filteredInternalSchedule.map((dayData) => (
                                <div
                                    key={dayData.day}
                                    className="flex flex-col h-full rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 shadow-sm overflow-hidden"
                                >
                                    {/* Day header */}
                                    <div className="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/30 dark:bg-gray-800/50">
                                        <div className="flex items-center gap-4">
                                            <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${DAY_COLORS[dayData.shortDay] ?? 'from-gray-400 to-gray-500'} text-white font-bold text-sm shadow-sm`}>
                                                {dayData.shortDay}
                                            </div>
                                            <div>
                                                <h3 className="text-base font-bold text-gray-900 dark:text-white">{dayData.day}</h3>
                                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                                    {dayData.entries.length} {dayData.entries.length === 1 ? 'entry' : 'entries'}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Classes Cards */}
                                    <div className="p-4 bg-gray-50/10 dark:bg-gray-800/20 flex-1 flex flex-col gap-4">
                                        {dayData.entries.map((entry) => (
                                            <ScheduleCard key={entry.id} item={entry} dayShort={dayData.shortDay} isInternalView={true} />
                                        ))}
                                    </div>

                                    {/* Day Footer */}
                                    <div className="px-5 py-3 border-t border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/80 text-right">
                                        <span className="inline-flex items-center rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-500/10 dark:bg-zinc-400/10 dark:text-zinc-400 dark:ring-zinc-400/30">
                                            Total: {dayData.entries.reduce((s, e) => s + (e.hours || e.requiredHours || 0), 0)} {dayData.entries.reduce((s, e) => s + (e.hours || e.requiredHours || 0), 0) === 1 ? 'hour' : 'hours'}
                                        </span>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="col-span-full flex flex-col items-center justify-center rounded-3xl border border-dashed border-gray-300 dark:border-gray-700 py-20 text-center bg-white dark:bg-gray-800/80">
                                <div className="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 mb-4">
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                <p className="text-sm font-semibold text-gray-600 dark:text-gray-300">No internal schedule found</p>
                                <p className="mt-1 text-xs text-gray-400 dark:text-gray-500">Your internal (operational) schedule has not been configured yet.</p>
                            </div>
                        )}
                    </div>
                </>
            )}

            <ScrollToTop />
        </AuthenticatedLayout>
    );
}
