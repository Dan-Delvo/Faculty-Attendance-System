import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function AdminForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('admin.password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Admin – Forgot Password" />

            <div className="space-y-1.5 border-b border-gray-100 dark:border-gray-700/50 pb-6 mb-6">
                <h3 className="text-xl font-extrabold text-[#7a1315] dark:text-[#cc2127] tracking-tight">
                    Reset Admin Password
                </h3>
                <p className="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Enter your admin email and we'll send a password reset link.
                </p>
            </div>

            {status && (
                <div className="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 p-4 border border-emerald-200 dark:border-emerald-800">
                    <div className="flex">
                        <div className="flex-shrink-0">
                            <svg className="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clipRule="evenodd" />
                            </svg>
                        </div>
                        <div className="ml-3">
                            <p className="text-sm font-medium text-emerald-800 dark:text-emerald-200">{status}</p>
                        </div>
                    </div>
                </div>
            )}

            <form onSubmit={submit} className="space-y-6">
                {/* Email Field */}
                <div>
                    <InputLabel htmlFor="email" value="Admin Email Address" className="text-gray-700 dark:text-gray-300 font-bold mb-2" />

                    <div className="relative">
                        <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg className="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M3 4a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2H3zm14 2L10 11.5 3 6v-.5h14V6z" />
                            </svg>
                        </div>
                        <TextInput
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="block w-full rounded-xl border-0 py-3 pl-11 pr-4 text-gray-900 ring-1 ring-inset ring-gray-300 dark:bg-gray-800/50 dark:text-white dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#7a1315] dark:focus:ring-[#cc2127] sm:text-sm sm:leading-6 transition-all duration-300 shadow-sm"
                            placeholder="admin@example.com"
                            autoComplete="username"
                            isFocused={true}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                    </div>
                    <InputError message={errors.email} className="mt-2" />
                </div>

                {/* Submit */}
                <div className="pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="group relative flex w-full justify-center rounded-xl bg-gradient-to-r from-[#7a1315] to-[#a81a1e] px-4 py-3.5 text-sm font-black text-white shadow-[0_4px_14px_0_rgba(122,19,21,0.39)] hover:shadow-[0_6px_20px_rgba(122,19,21,0.23)] hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        {processing ? (
                            <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        ) : (
                            <span className="flex items-center gap-2 tracking-wide uppercase">
                                Send Reset Link
                                <svg className="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" strokeWidth={3} stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </span>
                        )}
                    </button>
                </div>

                {/* Back to login */}
                <div className="text-center">
                    <Link
                        href={route('admin.login')}
                        className="text-[13px] font-semibold text-[#7a1315] dark:text-[#cc2127] hover:text-red-900 dark:hover:text-red-400 transition-colors inline-flex items-center gap-1"
                    >
                        <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Back to Admin Login
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
