import { Link } from '@inertiajs/react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import LandingLayout from '@/Layouts/LandingLayout';

export default function Welcome() {
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
                    {/* Faculty Login */}
                    <Link
                        href={route('login')}
                        className="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-[#7a1315] to-[#cc2127] px-8 py-4 text-sm font-bold text-white shadow-lg shadow-red-900/20 transition-all hover:scale-105 hover:shadow-xl hover:shadow-red-900/30 ring-1 ring-inset ring-white/10"
                    >
                        <svg className="h-4 w-4 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Faculty Login
                    </Link>

                    {/* Admin Login */}
                    <Link
                        href={route('admin.login')}
                        className="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-white dark:bg-gray-800 px-8 py-4 text-sm font-bold text-gray-900 dark:text-white shadow-md border border-gray-200 dark:border-gray-700 transition-all hover:bg-gray-50 dark:hover:bg-gray-700 hover:scale-105"
                    >
                        <svg className="h-4 w-4 opacity-70" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clipRule="evenodd" />
                        </svg>
                        Admin Login
                    </Link>
                </nav>
            </div>
        </LandingLayout>
    );
}
