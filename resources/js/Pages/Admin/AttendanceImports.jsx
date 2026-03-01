import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Pagination from '@/Components/Pagination';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, router, useForm } from '@inertiajs/react';
import { useMemo } from 'react';

const STATUS_STYLES = {
    pending: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-300',
    processing: 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-300',
    completed: 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-300',
    failed: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/30 dark:text-red-300',
};

function StatusBadge({ status }) {
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset capitalize ${STATUS_STYLES[status] ?? STATUS_STYLES.pending}`}>
            {status}
        </span>
    );
}

function formatDateTime(value) {
    if (!value) return '—';

    return new Date(value).toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export default function AttendanceImports({ batches, filters }) {
    const form = useForm({
        file: null,
    });

    const currentPage = useMemo(() => Number(batches?.current_page ?? 1), [batches]);
    const perPage = useMemo(() => Number(filters?.per_page ?? batches?.per_page ?? 10), [filters, batches]);

    const submit = (event) => {
        event.preventDefault();

        form.post(route('admin.attendance-imports.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => form.reset('file'),
        });
    };

    const handlePageChange = (page) => {
        router.get(
            route('admin.attendance-imports.index'),
            { page, per_page: perPage },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const handlePerPageChange = (nextPerPage) => {
        router.get(
            route('admin.attendance-imports.index'),
            { page: 1, per_page: nextPerPage },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="w-full flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                            Attendance Log Import
                        </h1>
                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Upload faculty biometric logs, save batch details, and insert records into biometric logs.
                        </p>
                    </div>
                    <a
                        href={route('admin.attendance-imports.template')}
                        className="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        Download Template
                    </a>
                </div>
            }
        >
            <Head title="Attendance Log Import" />

            <div className="space-y-6">
                <div className="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                    <h2 className="text-base font-semibold text-gray-900 dark:text-white">Import CSV/XLSX File</h2>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Required columns: biometric_id, log_datetime, log_type. Optional: device_id.
                    </p>

                    <form onSubmit={submit} className="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div className="flex-1">
                            <label htmlFor="biometric-log-file" className="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                CSV or Excel File
                            </label>
                            <input
                                id="biometric-log-file"
                                type="file"
                                accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                                onChange={(event) => form.setData('file', event.target.files?.[0] ?? null)}
                                className="block w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-gray-700 dark:file:bg-gray-600 dark:file:text-gray-100"
                            />
                            <InputError message={form.errors.file} className="mt-1" />
                        </div>

                        <PrimaryButton type="submit" disabled={form.processing || !form.data.file}>
                            {form.processing ? 'Importing…' : 'Import Logs'}
                        </PrimaryButton>
                    </form>
                </div>

                <div className="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                    <h2 className="text-base font-semibold text-gray-900 dark:text-white">Import Batches</h2>

                    <div className="mt-4 overflow-x-auto">
                        <table className="w-full min-w-[920px] text-sm">
                            <thead>
                                <tr className="border-b border-gray-200 dark:border-gray-700 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    <th className="py-3 pr-3">File</th>
                                    <th className="py-3 px-3">Status</th>
                                    <th className="py-3 px-3 text-right">Total</th>
                                    <th className="py-3 px-3 text-right">Processed</th>
                                    <th className="py-3 px-3 text-right">Failed</th>
                                    <th className="py-3 px-3 text-right">Duplicates</th>
                                    <th className="py-3 px-3">Started</th>
                                    <th className="py-3 pl-3">Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                {batches?.data?.length > 0 ? (
                                    batches.data.map((batch) => (
                                        <tr key={batch.id} className="border-b border-gray-100 dark:border-gray-700/80 text-gray-700 dark:text-gray-200">
                                            <td className="py-3 pr-3 align-top">
                                                <p className="font-semibold text-gray-800 dark:text-gray-100">{batch.file_name}</p>
                                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    Imported by: {batch.importedBy?.email ?? 'System'}
                                                </p>
                                            </td>
                                            <td className="py-3 px-3 align-top">
                                                <StatusBadge status={batch.status} />
                                            </td>
                                            <td className="py-3 px-3 text-right align-top">{batch.total_records}</td>
                                            <td className="py-3 px-3 text-right align-top">{batch.processed_records}</td>
                                            <td className="py-3 px-3 text-right align-top">{batch.failed_records}</td>
                                            <td className="py-3 px-3 text-right align-top">{batch.duplicate_records}</td>
                                            <td className="py-3 px-3 align-top">{formatDateTime(batch.started_at)}</td>
                                            <td className="py-3 pl-3 align-top">
                                                {formatDateTime(batch.completed_at)}
                                                {batch.error_log && (
                                                    <p className="mt-1 text-xs text-red-600 dark:text-red-400 line-clamp-2">
                                                        {batch.error_log}
                                                    </p>
                                                )}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={8} className="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No import batches yet.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <Pagination
                        currentPage={currentPage}
                        totalItems={Number(batches?.total ?? 0)}
                        perPage={Number(perPage)}
                        onPageChange={handlePageChange}
                        onPerPageChange={handlePerPageChange}
                        perPageOptions={[5, 10, 25, 50]}
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
