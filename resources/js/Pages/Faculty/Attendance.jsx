import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Pagination from '@/Components/Pagination';

// ── Status badge colour helper ──────────────────────────────────────────────
function statusStyle(status = '') {
    const s = status.toLowerCase();
    if (s === 'present' || s === 'on-time')
        return 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-400';
    if (s.includes('late') && s.includes('early'))
        return 'bg-orange-50 text-orange-700 ring-orange-500/20 dark:bg-orange-900/30 dark:text-orange-400';
    if (s.includes('late') || s.includes('tardy'))
        return 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400';
    if (s.includes('early') || s.includes('undertime'))
        return 'bg-orange-50 text-orange-700 ring-orange-500/20 dark:bg-orange-900/30 dark:text-orange-400';
    if (s === 'absent')
        return 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/30 dark:text-red-400';
    if (s === 'holiday')
        return 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-400';
    if (s.includes('missing') || s.includes('check'))
        return 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-900/30 dark:text-rose-400';
    // default
    return 'bg-gray-50 text-gray-700 ring-gray-500/20 dark:bg-gray-800 dark:text-gray-300';
}

export default function FacultyAttendance({ attendanceLogs }) {
    const { auth } = usePage().props;
    const userName = auth.faculty ? `${auth.faculty.first_name} ${auth.faculty.last_name}` : 'Faculty Member';

    // Pagination & Filtering Logic
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);
    const [dateRange, setDateRange] = useState({ start: '', end: '' });
    const [sourceFilter, setSourceFilter] = useState('all');

    // Filter logs based on date picker and source
    const filteredLogs = attendanceLogs.filter(log => {
        if (sourceFilter === 'online' && !log.online_attendance) return false;
        if (sourceFilter === 'biometric' && log.online_attendance) return false;

        if (!dateRange.start && !dateRange.end) return true;

        let valid = true;
        const logDate = new Date(log.raw_date).getTime();
        if (dateRange.start && logDate < new Date(dateRange.start).getTime()) valid = false;
        if (dateRange.end && logDate > new Date(dateRange.end).getTime()) valid = false;
        return valid;
    });

    // ── Summary stats (over filtered window) ───────────────────────────────
    const summary = filteredLogs.reduce((acc, log) => {
        const s = (log.status || '').toLowerCase();
        if (s === 'absent') acc.daysAbsent++;
        if (s.includes('late') || s.includes('tardy')) {
            acc.timesLate++;
            acc.totalLateMinutes += log.late_minutes || 0;
        }
        if (s.includes('early') || s.includes('undertime')) {
            acc.timesUndertime++;
            acc.totalUndertimeMinutes += log.undertime_minutes || 0;
        }
        if ((log.night_minutes || 0) > 0) {
            acc.timesNight++;
            acc.totalNightMinutes += log.night_minutes;
        }
        if ((log.overtime_minutes || 0) > 0) {
            acc.timesOvertime++;
            acc.totalOvertimeMinutes += log.overtime_minutes;
        }
        if ((log.overtime_night_minutes || 0) > 0) {
            acc.timesOvertimeNight++;
            acc.totalOvertimeNightMinutes += log.overtime_night_minutes;
        }
        return acc;
    }, {
        daysAbsent: 0,
        timesLate: 0, totalLateMinutes: 0,
        timesUndertime: 0, totalUndertimeMinutes: 0,
        timesNight: 0, totalNightMinutes: 0,
        timesOvertime: 0, totalOvertimeMinutes: 0,
        timesOvertimeNight: 0, totalOvertimeNightMinutes: 0,
    });

    const fmtMins = (m) => {
        const h = Math.floor(m / 60), rem = m % 60;
        return h > 0 ? `${h}h ${rem}m` : `${rem} mins.`;
    };

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

            {/* ── Summary Panel ────────────────────────────────────────── */}
            <div className="mb-6 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 bg-white dark:bg-gray-800/80 shadow-sm overflow-hidden">
                <div className="px-5 py-3 border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/60 dark:bg-gray-800/40">
                    <h2 className="text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Summary</h2>
                </div>
                <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 divide-x divide-y divide-gray-100 dark:divide-gray-700/50">
                    {[
                        { label: 'Days Absent', value: summary.daysAbsent, sub: null, color: 'text-red-600 dark:text-red-400' },
                        { label: 'Times Tardy', value: summary.timesLate, sub: fmtMins(summary.totalLateMinutes), color: 'text-amber-600 dark:text-amber-400' },
                        { label: 'Under Time', value: summary.timesUndertime, sub: fmtMins(summary.totalUndertimeMinutes), color: 'text-orange-600 dark:text-orange-400' },
                        { label: 'Nights Rendered', value: summary.timesNight, sub: fmtMins(summary.totalNightMinutes), color: 'text-violet-600 dark:text-violet-400' },
                        { label: 'Overtime Rendered', value: summary.timesOvertime, sub: fmtMins(summary.totalOvertimeMinutes), color: 'text-emerald-600 dark:text-emerald-400' },
                        { label: 'OT Nights', value: summary.timesOvertimeNight, sub: fmtMins(summary.totalOvertimeNightMinutes), color: 'text-indigo-600 dark:text-indigo-400' },
                    ].map(({ label, value, sub, color }) => (
                        <div key={label} className="px-4 py-4">
                            <p className="text-xs font-semibold text-gray-500 dark:text-gray-400 leading-tight">{label}</p>
                            <p className={`mt-1 text-2xl font-extrabold tabular-nums ${color}`}>{value}</p>
                            {sub && <p className="mt-0.5 text-xs text-gray-400 dark:text-gray-500">({sub})</p>}
                        </div>
                    ))}
                </div>
            </div>

            <div className="bg-white dark:bg-slate-800/80 shadow-sm ring-1 ring-gray-900/5 sm:rounded-2xl overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-slate-700/80">
                        <thead className="bg-gray-50 dark:bg-slate-800/90 border-b border-gray-200 dark:border-slate-700">
                            <tr>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-slate-300 uppercase tracking-widest">
                                    Date
                                </th>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-slate-300 uppercase tracking-widest">
                                    Subject
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
                                        {/* Subject column */}
                                        <td className="px-6 py-4 text-sm">
                                            {log.subjects && log.subjects.length > 0 ? (
                                                <div className="flex flex-col gap-1">
                                                    {log.subjects.map((s, i) => (
                                                        <div key={i}>
                                                            <span className="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-bold bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 ring-1 ring-inset ring-indigo-300/50 dark:ring-indigo-500/30">
                                                                {s.code}
                                                            </span>
                                                            {s.desc && (
                                                                <div className="mt-0.5 text-[11px] text-gray-400 dark:text-slate-500 truncate max-w-[140px]" title={s.desc}>
                                                                    {s.desc}
                                                                </div>
                                                            )}
                                                        </div>
                                                    ))}
                                                </div>
                                            ) : (
                                                <span className="text-gray-300 dark:text-slate-600">—</span>
                                            )}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-slate-400">
                                            <div className="flex items-center gap-2">
                                                <span className={`inline-flex items-center rounded-md px-2.5 py-1 text-xs font-black uppercase tracking-wider ring-1 ring-inset ${statusStyle(log.status)}`}>
                                                    {log.status}
                                                </span>
                                                {log.online_attendance && (
                                                    <span className="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-500/30">
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
                                    <td colSpan={7} className="py-16 text-center text-gray-500 dark:text-slate-400">
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
