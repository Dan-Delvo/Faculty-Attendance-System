import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ScrollToTop from '@/Components/ScrollToTop';
import { Head, Link } from '@inertiajs/react';



const DAY_COLORS = {
    Mon: 'from-blue-500 to-blue-600',
    Tue: 'from-violet-500 to-violet-600',
    Wed: 'from-emerald-500 to-emerald-600',
    Thu: 'from-amber-500 to-amber-600',
    Fri: 'from-rose-500 to-rose-600',
    Sat: 'from-cyan-500 to-cyan-600',
    Sun: 'from-gray-400 to-gray-500',
};

export default function Schedule({ weeklySchedule, facultyName }) {
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



            {/* ── Weekly Schedule ─────────────────────── */}
            <div className="space-y-4">
                {weeklySchedule.length > 0 ? (
                    weeklySchedule.map((dayData) => (
                        <div
                            key={dayData.day}
                            className="rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 shadow-sm overflow-hidden"
                        >
                            {/* Day header */}
                            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/30 dark:bg-gray-800/50">
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
                                <div className="text-right">
                                    <span className="inline-flex items-center rounded-md bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-500/10 dark:bg-zinc-400/10 dark:text-zinc-400 dark:ring-zinc-400/30">
                                        Total: {dayData.classes.reduce((s, c) => s + c.hours, 0)} hours
                                    </span>
                                </div>
                            </div>

                            {/* Classes Cards */}
                            <div className="p-4 sm:p-6 bg-gray-50/10 dark:bg-gray-800/20">
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {dayData.classes.map((cls) => (
                                        <div
                                            key={cls.id}
                                            className="group relative flex flex-col justify-between rounded-xl border border-gray-200/80 dark:border-gray-700/80 bg-white dark:bg-gray-800 p-5 shadow-sm hover:shadow-md hover:border-blue-200 dark:hover:border-blue-700/50 transition-all duration-200"
                                        >
                                            {/* Top Section: Subject & Room */}
                                            <div className="flex justify-between items-start mb-4">
                                                <div className="flex gap-3">
                                                    <div className={`mt-1 h-3 w-3 shrink-0 rounded-full bg-gradient-to-r ${DAY_COLORS[dayData.shortDay] ?? 'from-gray-400 to-gray-500'} shadow-sm`} />
                                                    <div>
                                                        <h4 className="font-bold text-gray-900 dark:text-white leading-tight pr-2">
                                                            {cls.subject}
                                                        </h4>
                                                        <p className="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                                            {cls.code}
                                                        </p>
                                                    </div>
                                                </div>

                                            </div>

                                            {/* Bottom Section: Time & Duration */}
                                            <div className="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
                                                <div>
                                                    <div className="flex items-center gap-1.5 text-sm font-bold text-gray-900 dark:text-white">
                                                        <svg className="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                        </svg>
                                                        {cls.startTime} - {cls.endTime}
                                                    </div>
                                                    <div className="mt-0.5 text-xs text-gray-500 dark:text-gray-400 ml-5.5">
                                                        {cls.hours} {cls.hours === 1 ? 'hr' : 'hrs'}
                                                    </div>
                                                </div>

                                                <div className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600/50 text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                    <svg className="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor">
                                                        <path strokeLinecap="round" strokeLinejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                        <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                    </svg>
                                                    {cls.room}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
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



            <ScrollToTop />
        </AuthenticatedLayout>
    );
}
