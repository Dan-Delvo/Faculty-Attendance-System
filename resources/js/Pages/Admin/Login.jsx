import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';

export default function AdminLogin({ status }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('admin.login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Admin Login" />

            {/* Admin badge */}
            <div className="mb-6 flex items-center gap-2">
                <span className="inline-flex items-center gap-1.5 rounded-full bg-[#7a1315]/10 dark:bg-red-900/30 px-3 py-1 text-xs font-semibold text-[#7a1315] dark:text-red-400 ring-1 ring-[#7a1315]/20 dark:ring-red-700/40">
                    <svg className="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clipRule="evenodd" />
                    </svg>
                    Admin Portal
                </span>
            </div>

            {status && (
                <div className="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 px-4 py-3 text-sm font-medium text-green-700 dark:text-green-400">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <InputLabel htmlFor="email" value="Email address" />
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                        placeholder="admin@example.com"
                    />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Password" />
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                        placeholder="••••••••"
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="flex items-center justify-between">
                    <label className="flex items-center gap-2 cursor-pointer select-none">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        <span className="text-sm text-gray-600 dark:text-gray-400">
                            Remember me
                        </span>
                    </label>
                </div>

                <PrimaryButton className="w-full justify-center py-3" disabled={processing}>
                    {processing ? (
                        <span className="flex items-center gap-2">
                            <svg className="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            Signing in…
                        </span>
                    ) : (
                        'Sign in to Admin'
                    )}
                </PrimaryButton>
            </form>

            <p className="mt-8 text-center text-xs text-gray-400 dark:text-gray-600">
                This portal is restricted to authorized administrators only.
            </p>
        </GuestLayout>
    );
}
