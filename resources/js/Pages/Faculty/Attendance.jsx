import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Pagination from '@/Components/Pagination';

export default function FacultyAttendance({ attendanceLogs }) {
    const { auth } = usePage().props;
    const userName = auth.faculty ? `${auth.faculty.first_name} ${auth.faculty.last_name}` : 'Faculty Member';

    // Pagination & Filtering Logic
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);
    const [dateRange, setDateRange] = useState({ start: '', end: '' });
    const [sourceFilter, setSourceFilter] = useState('all'); // 'all' | 'biometric' | 'online'

    // Filter logs based on date picker and source
    const filteredLogs = attendanceLogs.filter(log => {
        // Source filter
        if (sourceFilter === 'online' && !log.online_attendance) return false;
        if (sourceFilter === 'biometric' && log.online_attendance) return false;

        if (!dateRange.start && !dateRange.end) return true;

        let valid = true;
        // Parse raw_date generated from backend (YYYY-MM-DD)
        const logDate = new Date(log.raw_date).getTime();

        if (dateRange.start) {
            const start = new Date(dateRange.start).getTime();
            if (logDate < start) valid = false;
        }
        if (dateRange.end) {
            // Need to make sure end date includes the whole day
            const end = new Date(dateRange.end).getTime();
            if (logDate > end) valid = false;
        }

        return valid;
    });

    const paginatedLogs = filteredLogs.slice(
        (currentPage - 1) * perPage,
        currentPage * perPage
    );

    return (
        <AuthenticatedLayout>
            <Head title="Attendance History" />

            <div className="mb-8">
                <Link
                    href={route('faculty.dashboard')}
                    className="inline-flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                >
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back to Dashboard
                </Link>
            </div>

            <div className="mb-8 relative isolate overflow-hidden rounded-3xl bg-white dark:bg-gray-800/60 p-8 shadow-sm border border-gray-200/60 dark:border-gray-700/60 backdrop-blur-xl">
                <div className="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-gradient-to-br from-[#7a1315]/10 to-[#cc2127]/5 blur-3xl transition-transform duration-700" />
                <h1 className="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                    {sourceFilter === 'online' ? 'Online Attendance History' : sourceFilter === 'biometric' ? 'Biometric Attendance History' : 'Attendance Match History'}
                </h1>
                <p className="mt-2 max-w-2xl text-base text-gray-500 dark:text-gray-400">
                    {sourceFilter === 'online'
                        ? 'Your approved online attendance records aligned with your internal work schedule.'
                        : sourceFilter === 'biometric'
                            ? 'Your matched attendance status from raw biometric logs aligned with your internal work schedule.'
                            : 'Your matched attendance status spanning your entire history. This uses your raw biometric logs and approved online attendance aligned with your internal work schedule.'}
                </p>

                {/* Source filter */}
                <div className="mt-5 flex items-center gap-2">
                    <span className="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Source:</span>
                    {[
                        { value: 'all', label: 'All' },
                        { value: 'biometric', label: 'Biometric' },
                        { value: 'online', label: 'Online' },
                    ].map(opt => (
                        <button
                            key={opt.value}
                            onClick={() => { setSourceFilter(opt.value); setCurrentPage(1); }}
                            className={
                                'rounded-lg px-3 py-1.5 text-xs font-bold transition-all duration-200 ' +
                                (sourceFilter === opt.value
                                    ? 'bg-[#7a1315] text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600')
                            }
                        >
                            {opt.label}
                        </button>
                    ))}
                </div>
            </div>

            <Pagination
                currentPage={currentPage}
                totalItems={filteredLogs.length}
                perPage={perPage}
                onPageChange={setCurrentPage}
                onPerPageChange={(size) => {
                    setPerPage(size);
                    setCurrentPage(1);
                }}
                showDateRange={true}
                dateRange={dateRange}
                onDateRangeChange={(newRange) => {
                    setDateRange(newRange);
                    setCurrentPage(1);
                }}
                className="px-6 border-t border-gray-200/60 dark:border-slate-700/80"
            />

            <div className="bg-white dark:bg-slate-800/80 shadow-sm ring-1 ring-gray-900/5 sm:rounded-2xl overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-slate-700/80">
                        <thead className="bg-gray-50 dark:bg-slate-800/90 border-b border-gray-200 dark:border-slate-700">
                            <tr>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-slate-300 uppercase tracking-widest">
                                    Date
                                </th>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-slate-300 uppercase tracking-widest">
                                    Status
                                </th>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-slate-300 uppercase tracking-widest">
                                    Schedule (In - Out)
                                </th>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-slate-300 uppercase tracking-widest">
                                    Actual (In - Out)
                                </th>
                                <th scope="col" className="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-slate-300 uppercase tracking-widest">
                                    Deductions
                                </th>
                                <th scope="col" className="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-slate-300 uppercase tracking-widest">
                                    Rendered
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 dark:divide-slate-700/80 bg-transparent">
                            {paginatedLogs.length > 0 ? (
                                paginatedLogs.map((log, index) => (
                                    <tr key={index} className="transition-all duration-300 hover:bg-gray-50 dark:hover:bg-slate-700/50 group">
                                        <td className="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                            <div className="font-bold text-gray-900 dark:text-white group-hover:text-[#7a1315] dark:group-hover:text-red-400 transition-colors">{log.date}</div>
                                            <div className="text-xs text-gray-500 dark:text-slate-400">{log.dayOfWeek}</div>
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-slate-400">
                                            <div className="flex items-center gap-2">
                                                <span className={`inline-flex items-center rounded-md px-2.5 py-1 text-xs font-black uppercase tracking-wider ring-1 ring-inset ${log.status === 'Present' || log.status === 'On-time' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-400' :
                                                    log.status.includes('Late') ? 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400' :
                                                        'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/30 dark:text-red-400'
                                                    }`}>
                                                    {log.status}
                                                </span>
                                                {log.online_attendance && (
                                                    <span className="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-500/30">
                                                        <svg className="h-3 w-3" fill="none" viewBox="0 0 24 24" strokeWidth={2} stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                                                        </svg>
                                                        Online
                                                    </span>
                                                )}
                                            </div>
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-600 dark:text-slate-300">
                                            {log.expected_time_in} <span className="text-gray-300 dark:text-slate-500 mx-1">-</span> {log.expected_time_out}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">
                                            <span className={log.late_minutes > 0 ? 'text-amber-600 dark:text-amber-400' : ''}>{log.actual_time_in}</span>
                                            <span className="text-gray-300 dark:text-slate-500 mx-1">-</span>
                                            <span className={log.undertime_minutes > 0 ? 'text-red-600 dark:text-red-400' : ''}>{log.actual_time_out}</span>
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-center">
                                            {log.late_minutes === 0 && log.undertime_minutes === 0 ? (
                                                <span className="inline-flex items-center justify-center h-6 w-6 rounded-full bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500">
                                                    <svg className="h-3 w-3" fill="none" viewBox="0 0 24 24" strokeWidth={3} stroke="currentColor">
                                                        <path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12h15" />
                                                    </svg>
                                                </span>
                                            ) : (
                                                <div className="flex flex-col gap-1 items-center">
                                                    {log.late_minutes > 0 && <span className="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-300">{log.late_minutes}m Late</span>}
                                                    {log.undertime_minutes > 0 && <span className="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-900 dark:bg-red-900/40 dark:text-red-300">{log.undertime_minutes}m Early</span>}
                                                </div>
                                            )}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-right">
                                            <div className="inline-flex items-baseline gap-1 bg-gray-100 dark:bg-slate-800 px-3 py-1.5 rounded-lg border border-gray-200/50 dark:border-slate-700/50">
                                                <span className="text-sm font-black text-gray-900 dark:text-white drop-shadow-sm">{log.total_hours}</span>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={6} className="py-16 text-center text-gray-500 dark:text-slate-400">
                                        <div className="flex flex-col items-center justify-center">
                                            <div className="flex h-16 w-16 items-center justify-center rounded-full bg-gray-50 dark:bg-slate-800 ring-1 ring-gray-100 dark:ring-slate-700 mb-4">
                                                <svg className="h-8 w-8 text-gray-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" strokeWidth={1.5}>
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            </div>
                                            <h3 className="mt-2 text-base font-bold text-gray-900 dark:text-white">
                                                {sourceFilter === 'online' ? 'No Online Attendance' : sourceFilter === 'biometric' ? 'No Biometric Records' : 'No Attendance Records'}
                                            </h3>
                                            <p className="mt-1 text-sm text-gray-500 dark:text-slate-400 max-w-sm">
                                                {sourceFilter === 'online'
                                                    ? 'You don\'t have any approved online attendance records for this period.'
                                                    : sourceFilter === 'biometric'
                                                        ? 'You don\'t have any matched schedules mapped to your biometrics for this period.'
                                                        : 'You don\'t have any attendance records for this period.'}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination
                    currentPage={currentPage}
                    totalItems={filteredLogs.length}
                    perPage={perPage}
                    onPageChange={setCurrentPage}
                    onPerPageChange={(size) => {
                        setPerPage(size);
                        setCurrentPage(1);
                    }}
                    showDateRange={true}
                    dateRange={dateRange}
                    onDateRangeChange={(newRange) => {
                        setDateRange(newRange);
                        setCurrentPage(1);
                    }}
                    className="px-6 border-t border-gray-200/60 dark:border-slate-700/80"
                />

            </div>
        </AuthenticatedLayout>
    );
}
