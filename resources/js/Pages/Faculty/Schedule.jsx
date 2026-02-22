import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

const STATUS_COLORS = {
    present: { label: 'Present', bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-700 dark:text-emerald-400' },
    late: { label: 'Late', bg: 'bg-amber-100 dark:bg-amber-900/30', text: 'text-amber-700 dark:text-amber-400' },
    undertime: { label: 'Undertime', bg: 'bg-orange-100 dark:bg-orange-900/30', text: 'text-orange-700 dark:text-orange-400' },
    late_undertime: { label: 'Late & Undertime', bg: 'bg-red-100 dark:bg-red-900/30', text: 'text-red-700 dark:text-red-400' },
    absent: { label: 'Absent', bg: 'bg-gray-100 dark:bg-gray-800', text: 'text-gray-600 dark:text-gray-400' },
};

const DAY_COLORS = {
    Mon: 'from-blue-500 to-blue-600',
    Tue: 'from-violet-500 to-violet-600',
    Wed: 'from-emerald-500 to-emerald-600',
    Thu: 'from-amber-500 to-amber-600',
    Fri: 'from-rose-500 to-rose-600',
    Sat: 'from-cyan-500 to-cyan-600',
    Sun: 'from-gray-400 to-gray-500',
};

export default function Schedule({ weeklySchedule, attendanceRecords, facultyName }) {
    const [activeTab, setActiveTab] = useState('schedule'); // 'schedule' | 'attendance'
    const [attPage, setAttPage] = useState(1);
    const ATT_PER_PAGE = 8;

    const totalAttPages = Math.ceil(attendanceRecords.length / ATT_PER_PAGE);
    const paginatedAttendance = attendanceRecords.slice(
        (attPage - 1) * ATT_PER_PAGE,
        attPage * ATT_PER_PAGE,
    );

    // Count total weekly hours
    const totalWeeklyHours = weeklySchedule.reduce(
        (sum, day) => sum + day.classes.reduce((s, c) => s + c.hours, 0),
        0,
    );

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
                        <p className="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{weeklySchedule.length}</p>
                    </div>
                    <div className="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 px-5 py-3 shadow-sm text-center">
                        <p className="text-xs font-medium text-gray-500 dark:text-gray-400">Weekly Hours</p>
                        <p className="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{totalWeeklyHours}</p>
                    </div>
                </div>
            </div>

            {/* ── Tab Switcher ────────────────────────── */}
            <div className="flex gap-2 mb-6">
                {[
                    {
                        key: 'schedule', label: 'Weekly Schedule', icon: (
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        )
                    },
                    {
                        key: 'attendance', label: 'Attendance Log', icon: (
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25" />
                            </svg>
                        )
                    },
                ].map((tab) => (
                    <button
                        key={tab.key}
                        onClick={() => setActiveTab(tab.key)}
                        className={`inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-xs font-semibold transition-all duration-200 ${activeTab === tab.key
                                ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-md'
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'
                            }`}
                    >
                        {tab.icon}
                        {tab.label}
                    </button>
                ))}
            </div>

            {/* ── Weekly Schedule Tab ─────────────────── */}
            {activeTab === 'schedule' && (
                <div className="space-y-4">
                    {weeklySchedule.length > 0 ? (
                        weeklySchedule.map((dayData) => (
                            <div
                                key={dayData.day}
                                className="rounded-3xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 shadow-sm overflow-hidden"
                            >
                                {/* Day header */}
                                <div className="flex items-center gap-4 px-6 py-4 border-b border-gray-100 dark:border-gray-700/50">
                                    <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br ${DAY_COLORS[dayData.shortDay] ?? 'from-gray-400 to-gray-500'} text-white font-bold text-sm shadow-md`}>
                                        {dayData.shortDay}
                                    </div>
                                    <div>
                                        <h3 className="text-sm font-bold text-gray-900 dark:text-white">{dayData.day}</h3>
                                        <p className="text-xs text-gray-500 dark:text-gray-400">
                                            {dayData.classes.length} {dayData.classes.length === 1 ? 'class' : 'classes'} · {dayData.classes.reduce((s, c) => s + c.hours, 0)} hours
                                        </p>
                                    </div>
                                </div>

                                {/* Classes */}
                                <div className="divide-y divide-gray-50 dark:divide-gray-700/30">
                                    {dayData.classes.map((cls) => (
                                        <div key={cls.id} className="flex items-center justify-between px-6 py-4 hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                            <div className="flex items-center gap-4">
                                                <div className="h-10 w-1 rounded-full bg-gradient-to-b from-blue-400 to-blue-600" />
                                                <div>
                                                    <p className="text-sm font-bold text-gray-900 dark:text-white">{cls.subject}</p>
                                                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                        {cls.code} · <span className="inline-flex items-center gap-0.5">
                                                            <svg className="h-3 w-3" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                                <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                            </svg>
                                                            {cls.room}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-sm font-bold text-gray-900 dark:text-white">{cls.startTime}</p>
                                                <p className="text-xs text-gray-400 dark:text-gray-500">to {cls.endTime}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="flex flex-col items-center justify-center rounded-3xl border border-dashed border-gray-300 dark:border-gray-700 py-20 text-center bg-white dark:bg-gray-800/80">
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

            {/* ── Attendance Log Tab ─────────────────── */}
            {activeTab === 'attendance' && (
                <>
                    <div className="rounded-3xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 shadow-sm overflow-hidden">
                        {paginatedAttendance.length > 0 ? (
                            <div className="divide-y divide-gray-100 dark:divide-gray-700/50">
                                {paginatedAttendance.map((record) => {
                                    const style = STATUS_COLORS[record.status] ?? STATUS_COLORS['present'];

                                    return (
                                        <div
                                            key={record.id}
                                            className="px-6 py-4 hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors"
                                        >
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-4">
                                                    {/* Date badge */}
                                                    <div className="flex flex-col items-center justify-center h-12 w-14 rounded-xl bg-gray-100 dark:bg-gray-700/60">
                                                        <span className="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase">
                                                            {record.dayOfWeek?.slice(0, 3)}
                                                        </span>
                                                        <span className="text-xs font-bold text-gray-900 dark:text-white">
                                                            {record.date?.split(' ')[1]?.replace(',', '')}
                                                        </span>
                                                    </div>

                                                    {/* Subject info */}
                                                    <div>
                                                        <div className="flex items-center gap-2">
                                                            <span className="text-sm font-bold text-gray-900 dark:text-white">
                                                                {record.subject}
                                                            </span>
                                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${style.bg} ${style.text}`}>
                                                                {style.label}
                                                            </span>
                                                        </div>
                                                        <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                            {record.subjectCode} · {record.date}
                                                        </p>
                                                    </div>
                                                </div>

                                                {/* Times and hours */}
                                                <div className="text-right">
                                                    <p className="text-sm font-bold text-gray-900 dark:text-white">
                                                        {record.timeIn} – {record.timeOut}
                                                    </p>
                                                    <p className="text-xs text-gray-400 dark:text-gray-500">
                                                        {record.hoursRendered}h / {record.requiredHours}h
                                                        {record.lateMinutes > 0 && (
                                                            <span className="text-amber-500 ml-1">+{record.lateMinutes}m late</span>
                                                        )}
                                                    </p>
                                                </div>
                                            </div>

                                            {/* Remarks row */}
                                            {record.remarks && record.status !== 'present' && (
                                                <p className="mt-2 ml-[4.5rem] text-xs text-gray-400 dark:text-gray-500 italic">
                                                    {record.remarks}
                                                </p>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center py-20 text-center">
                                <div className="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 mb-4">
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25" />
                                    </svg>
                                </div>
                                <p className="text-sm font-semibold text-gray-600 dark:text-gray-300">No attendance records yet</p>
                                <p className="mt-1 text-xs text-gray-400 dark:text-gray-500">Records will appear here after your first class.</p>
                            </div>
                        )}
                    </div>

                    {/* Pagination */}
                    {totalAttPages > 1 && (
                        <div className="mt-6 flex items-center justify-between">
                            <button
                                onClick={() => setAttPage((p) => Math.max(1, p - 1))}
                                disabled={attPage === 1}
                                className="inline-flex items-center gap-1 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700"
                            >
                                <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                                Previous
                            </button>

                            <span className="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Page {attPage} of {totalAttPages}
                            </span>

                            <button
                                onClick={() => setAttPage((p) => Math.min(totalAttPages, p + 1))}
                                disabled={attPage === totalAttPages}
                                className="inline-flex items-center gap-1 rounded-xl px-4 py-2 text-xs font-semibold transition-all duration-200 disabled:opacity-30 disabled:cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700"
                            >
                                Next
                                <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    )}
                </>
            )}
        </AuthenticatedLayout>
    );
}
