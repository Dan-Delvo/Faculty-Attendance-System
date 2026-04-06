import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="flex flex-col lg:flex-row min-h-screen w-full font-sans antialiased text-gray-900 bg-gray-50 dark:bg-gray-900 selection:bg-[#7a1315] selection:text-white relative overflow-x-hidden">
            {/* Left side - Branding & Image (hidden on small screens) */}
            <div className="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-[#7a1315] via-[#a81a1e] to-[#cc2127] text-white flex-col justify-between">
                {/* Decorative background elements safely contained */}
                <div className="absolute inset-0 overflow-hidden pointer-events-none">
                    <div className="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] opacity-10 blur-2xl flex items-center justify-center">
                        <svg width="100%" height="100%" fill="currentColor" viewBox="0 0 200 200"><path d="M45.7,-76.3C58.9,-69.3,69.1,-56,76.5,-41.7C83.9,-27.4,88.5,-12.1,87.7,3.1C87,18.3,81,33.4,72.1,46.1C63.2,58.8,51.4,69.1,37.6,76.5C23.8,83.9,8,88.4,-7.8,88.3C-23.7,88.2,-39.7,83.5,-53.4,75C-67.1,66.5,-78.6,54.2,-85.4,39.6C-92.2,25,-94.3,8.1,-91.1,-7.8C-87.9,-23.7,-79.4,-38.6,-68.3,-50.2C-57.2,-61.8,-43.5,-70,-29.3,-75.4C-15.1,-80.8,0.7,-83.4,16.2,-81.2C31.7,-79,47.2,-72,45.7,-76.3Z" transform="translate(100 100)" /></svg>
                    </div>
                    <div className="absolute bottom-[-10%] left-[-10%] w-[60%] h-[60%] opacity-10 blur-3xl flex items-center justify-center">
                        <svg width="100%" height="100%" fill="currentColor" viewBox="0 0 200 200"><path d="M39.9,-65.7C52.8,-59,65.2,-50.1,72.7,-38.3C80.2,-26.5,82.8,-11.9,81.3,2.4C79.8,16.7,74.2,30.7,65.5,42.4C56.8,54.1,45,63.5,31.7,69.7C18.4,75.9,3.6,78.9,-10.9,76.7C-25.4,74.5,-39.6,67.1,-51.7,56.7C-63.8,46.3,-73.8,32.9,-78.7,17.7C-83.6,2.5,-83.4,-14.5,-77.1,-29.4C-70.8,-44.3,-58.4,-57.1,-44.3,-63.3C-30.2,-69.5,-14.4,-69.1,0.5,-69.8C15.4,-70.5,30.8,-72.3,39.9,-65.7Z" transform="translate(100 100)" /></svg>
                    </div>
                </div>

                <div className="p-12 relative z-10 flex flex-col h-full justify-center">
                    <div className="mb-10">
                        <Link href="/" className="inline-flex items-center gap-4 transition-transform hover:scale-105 active:scale-95">
                            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl">
                                <ApplicationLogo className="h-10 w-10 text-white fill-white" />
                            </div>
                            <span className="text-3xl font-black tracking-tight text-white drop-shadow-md">
                                FA-SYS
                            </span>
                        </Link>
                    </div>

                    <div className="space-y-6 max-w-xl">
                        <h1 className="text-5xl font-black tracking-tight leading-[1.1]">
                            PUP Taguig Part-Time Faculty <br />
                            <span className="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-yellow-500 drop-shadow-sm">
                                Attendance System
                            </span>
                        </h1>
                        <p className="text-lg text-red-100/90 font-medium leading-relaxed">
                            Streamlining academic schedules and part-time faculty attendance management. Designed for accuracy, built for convenience.
                        </p>
                    </div>

                    <div className="mt-20 flex items-center gap-4 text-sm text-red-200 font-semibold tracking-wide uppercase">
                        <div className="h-1 w-12 bg-gradient-to-r from-yellow-400 to-yellow-200 rounded-full"></div>
                        Official Portal for Academics
                    </div>
                </div>
            </div>

            {/* Right side - Form */}
            <div className="flex w-full lg:w-1/2 flex-col items-center justify-center p-6 sm:p-12 relative">
                {/* Decorative background blob for right side on dark mode constrained */}
                <div className="absolute inset-0 overflow-hidden pointer-events-none">
                    <div className="absolute top-0 right-0 h-full w-1/2 bg-gradient-to-bl from-red-900/10 to-transparent opacity-0 dark:opacity-100"></div>
                    <div className="absolute bottom-0 left-0 h-full w-1/2 bg-gradient-to-tr from-yellow-900/5 to-transparent opacity-0 dark:opacity-100"></div>
                </div>

                <div className="w-full max-w-md relative z-10 flex flex-col items-center lg:items-stretch">
                    {/* Mobile Header (visible only on small screens) */}
                    <div className="mb-10 flex flex-col items-center lg:hidden">
                        <Link href="/" className="transition-transform hover:scale-105">
                            <div className="flex h-20 w-20 items-center justify-center rounded-2xl bg-[#7a1315]/10 dark:bg-red-500/10 backdrop-blur-xl border border-[#7a1315]/20 dark:border-red-500/20 shadow-xl">
                                <ApplicationLogo className="h-12 w-12 text-[#7a1315] dark:text-red-500 fill-current" />
                            </div>
                        </Link>
                        <h2 className="mt-6 text-3xl font-black text-gray-900 dark:text-white text-center tracking-tight">
                            PUP Taguig
                        </h2>
                        <h3 className="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-[#7a1315] to-[#cc2127] dark:from-red-400 dark:to-yellow-500 text-center uppercase tracking-wide mt-1">
                            Part-Time Faculty Attendance System
                        </h3>
                    </div>

                    <div className="mb-8 lg:mb-10 text-center lg:text-left transition-all duration-300">
                        <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white mb-2 tracking-tight">
                            Welcome back
                        </h2>
                        <p className="text-gray-500 dark:text-gray-400 font-medium">
                            Please sign in to your account to continue.
                        </p>
                    </div>

                    <div className="w-full rounded-3xl bg-white/80 backdrop-blur-xl px-8 py-10 shadow-2xl shadow-gray-200/50 ring-1 ring-gray-900/5 dark:bg-gray-800/80 dark:shadow-gray-900/50 dark:ring-white/10 sm:px-10 transition-all duration-300">
                        {children}
                    </div>

                    {/* Footer text */}
                    <p className="mt-10 text-center text-sm font-medium text-gray-500 dark:text-gray-400">
                        &copy; {new Date().getFullYear()} Polytechnic University of the Philippines Taguig Campus.<br /> All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    );
}
