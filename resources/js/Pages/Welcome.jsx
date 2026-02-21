import { Link } from '@inertiajs/react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import LandingLayout from '@/Layouts/LandingLayout';

export default function Welcome({ auth }) {
    return (
        <LandingLayout title="Welcome to PUP Part-Time Faculty Attendance System">
            <div className="w-full max-w-4xl text-center">
                {/* Logo and Branding Header */}
                <div className="flex flex-col items-center justify-center mb-10 transition-transform duration-700 hover:scale-105">
                    <div className="flex h-24 w-24 items-center justify-center rounded-3xl bg-gradient-to-br from-[#7a1315] via-[#a81a1e] to-[#cc2127] shadow-xl shadow-red-900/20 mb-6 p-4 border border-white/10">
                        <ApplicationLogo className="h-full w-full text-white fill-white" />
                    </div>
                    <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 mb-8 shadow-sm">
                        <span className="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                        <span className="text-xs font-bold tracking-wide uppercase text-gray-900 dark:text-white">System Online</span>
                    </div>
                </div>

                {/* Hero Text */}
                <h1 className="text-5xl md:text-7xl font-black tracking-tighter mb-6 text-gray-900 dark:text-white">
                    PUP Taguig Faculty
                    <br />
                    <span className="text-[#7a1315] dark:text-[#ef4444]">Attendance System</span>
                </h1>
                <p className="mt-4 text-lg md:text-xl text-gray-900 dark:text-gray-200 font-semibold max-w-2xl mx-auto leading-relaxed mb-12">
                    The official portal for managing academic schedules, recording class attendance, and maintaining verifiable academic records.
                </p>

                {/* Action Buttons */}
                <nav className="flex flex-col sm:flex-row items-center justify-center gap-4">
                    {auth.user ? (
                        <Link
                            href={route('dashboard')}
                            className="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#7a1315] to-[#cc2127] px-8 py-4 text-sm font-bold text-white shadow-lg shadow-red-900/20 transition-all hover:scale-105 hover:shadow-xl hover:shadow-red-900/30 ring-1 ring-inset ring-white/10"
                        >
                            Go to Dashboard
                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </Link>
                    ) : (
                        <>
                            <Link
                                href={route('login')}
                                className="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#7a1315] to-[#cc2127] px-8 py-4 text-sm font-bold text-white shadow-lg shadow-red-900/20 transition-all hover:scale-105 hover:shadow-xl hover:shadow-red-900/30 ring-1 ring-inset ring-white/10"
                            >
                                Part-Time Faculty Login
                                <svg className="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                            </Link>
                            <Link
                                href={route('register')}
                                className="w-full sm:w-auto inline-flex items-center justify-center rounded-full bg-white dark:bg-gray-800 px-8 py-4 text-sm font-bold text-gray-900 dark:text-white shadow-md border border-gray-200 dark:border-gray-700 transition-all hover:bg-gray-50 dark:hover:bg-gray-700 hover:scale-105"
                            >
                                Register Account
                            </Link>
                        </>
                    )}
                </nav>
            </div>
        </LandingLayout>
    );
}
