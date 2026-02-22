import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

const STATUS_STYLES = {
    'on-time': { label: 'ON TIME', bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-700 dark:text-emerald-400' },
    'late': { label: 'LATE', bg: 'bg-red-100 dark:bg-red-900/30', text: 'text-red-700 dark:text-red-400' },
    'early-out': { label: 'EARLY OUT', bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-700 dark:text-amber-400' },
};

export default function BiometricLogs({ biometricLogs, monthlyAverages }) {
    const LOGS_PER_PAGE = 10;
    const [page, setPage] = useState(1);
    const [filter, setFilter] = useState('all'); // 'all' | 'check-in' | 'check-out'

    const filteredLogs = filter === 'all'
        ? biometricLogs
        : biometricLogs.filter((log) => log.type === filter);

    const totalPages = Math.ceil(filteredLogs.length / LOGS_PER_PAGE);
    const paginatedLogs = filteredLogs.slice(
        (page - 1) * LOGS_PER_PAGE,
        page * LOGS_PER_PAGE,
    );

    // Reset to page 1 when filter changes
    const handleFilter = (f) => {
        setFilter(f);
        setPage(1);
    };

    return (
        <AuthenticatedLayout>
            <Head title="Biometric Logs" />

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
                        Biometric Logs
                    </h1>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Complete history of your biometric check-in and check-out records.
                    </p>
                </div>

                {/* Summary chips */}
                <div className="flex gap-3">
                    <div className="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 px-5 py-3 shadow-sm text-center">
                        <p className="text-xs font-medium text-gray-500 dark:text-gray-400">Avg Check-In</p>
                        <p className="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{monthlyAverages.avgCheckIn}</p>
                    </div>
                    <div className="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 px-5 py-3 shadow-sm text-center">
                        <p className="text-xs font-medium text-gray-500 dark:text-gray-400">Avg Check-Out</p>
                        <p className="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{monthlyAverages.avgCheckOut}</p>
                    </div>
                </div>
            </div>

            {/* ── Filter Tabs ─────────────────────────── */}
            <div className="flex gap-2 mb-6">
                {[
                    { key: 'all', label: 'All' },
                    { key: 'check-in', label: 'Check-In' },
                    { key: 'check-out', label: 'Check-Out' },
                ].map((tab) => (
                    <button
                        key={tab.key}
                        onClick={() => handleFilter(tab.key)}
                        className={`rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-200 ${filter === tab.key
                                ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-md'
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
                            }`}
                    >
                        {tab.label}
                    </button>
                ))}

                <span className="ml-auto text-xs font-medium text-gray-400 dark:text-gray-500 self-center">
                    {filteredLogs.length} {filteredLogs.length === 1 ? 'entry' : 'entries'}
                </span>
            </div>

            {/* ── Logs Table ─────────────────────────── */}
            <div className="rounded-3xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 shadow-sm overflow-hidden">
                {paginatedLogs.length > 0 ? (
                    <div className="divide-y divide-gray-100 dark:divide-gray-700/50">
                        {paginatedLogs.map((log) => {
                            const isCheckIn = log.type === 'check-in';
                            const style = STATUS_STYLES[log.status] ?? STATUS_STYLES['on-time'];

                            return (
                                <div
                                    key={log.id}
                                    className="flex items-center justify-between px-6 py-4 hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors"
                                >
                                    <div className="flex items-center gap-4">
                                        {/* Icon */}
                                        <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${isCheckIn
                                                ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                : 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400'
                                            }`}>
                                            {isCheckIn ? (
                                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                                </svg>
                                            ) : (
                                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                                </svg>
                                            )}
                                        </div>

                                        {/* Details */}
                                        <div>
                                            <div className="flex items-center gap-2">
                                                <span className="text-sm font-bold text-gray-900 dark:text-white">
                                                    {isCheckIn ? 'Check In' : 'Check Out'}
                                                </span>
                                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${style.bg} ${style.text}`}>
                                                    {style.label}
                                                </span>
                                            </div>
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {log.device}
                                            </p>
                                        </div>
                                    </div>

                                    {/* Time & Date */}
                                    <div className="text-right">
                                        <p className="text-sm font-bold text-gray-900 dark:text-white">{log.timestamp}</p>
                                        <p className="text-xs text-gray-400 dark:text-gray-500">{log.date}</p>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <div className="flex flex-col items-center justify-center py-20 text-center">
                        <div className="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 mb-4">
                            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M7.864 4.243A7.5 7.5 0 0 1 19.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 0 0 4.5 10.5a48.667 48.667 0 0 0-1.26 5.198M12 8.25v.375a4.5 4.5 0 0 1-2.294 3.927l-.346.196a4.5 4.5 0 0 0-2.294 3.927V18" />
                            </svg>
                        </div>
                        <p className="text-sm font-semibold text-gray-600 dark:text-gray-300">No biometric logs found</p>
                        <p className="mt-1 text-xs text-gray-400 dark:text-gray-500">Records will appear here once available.</p>
                    </div>
                )}
            </div>

            {/* ── Pagination ─────────────────────────── */}
            {totalPages > 1 && (
                <div className="mt-6 flex items-center justify-between">
                    <button
                        onClick={() => setPage((p) => Math.max(1, p - 1))}
                        disabled={page === 1}
                        className="inline-flex items-center gap-1 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700"
                    >
                        <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                        Previous
                    </button>

                    <span className="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Page {page} of {totalPages}
                    </span>

                    <button
                        onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                        disabled={page === totalPages}
                        className="inline-flex items-center gap-1 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700"
                    >
                        Next
                        <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
