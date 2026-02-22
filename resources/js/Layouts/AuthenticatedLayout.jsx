import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
    const { auth } = usePage().props;
    const user = auth.user;
    const roles = auth.roles ?? [];

    // Determine the correct dashboard route based on user role
    const isFaculty = roles.includes('faculty');
    const dashboardRoute = isFaculty ? 'faculty.dashboard' : 'dashboard';
    const dashboardActive = isFaculty
        ? route().current('faculty.dashboard')
        : route().current('dashboard');

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans antialiased selection:bg-[#7a1315] selection:text-white transition-colors duration-300">
            {/* Top Navigation Bar */}
            <nav className="sticky top-0 z-40 w-full backdrop-blur-md bg-white/80 dark:bg-gray-900/80 border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between items-center">
                        <div className="flex items-center">
                            {/* Brand Logo */}
                            <div className="flex shrink-0 items-center">
                                <Link href="/" className="transition-transform hover:scale-105 active:scale-95">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#7a1315] to-[#cc2127] shadow-lg shadow-red-900/20">
                                        <ApplicationLogo className="h-6 w-6 text-white fill-white" />
                                    </div>
                                </Link>
                            </div>

                            {/* Desktop Links */}
                            <div className="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex h-16 items-center">
                                <NavLink
                                    href={route(dashboardRoute)}
                                    active={dashboardActive}
                                >
                                    Dashboard
                                </NavLink>
                            </div>
                        </div>

                        {/* Desktop User Menu */}
                        <div className="hidden sm:ms-6 sm:flex sm:items-center">
                            <div className="relative ms-3">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center gap-2 rounded-xl border border-transparent bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm font-semibold leading-4 text-gray-700 dark:text-gray-200 transition-all duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-[#a81a1e] focus:ring-offset-2 dark:focus:ring-offset-gray-900 shadow-sm"
                                            >
                                                {/* User Avatar Placeholder */}
                                                <div className="h-6 w-6 rounded-full bg-gradient-to-tr from-[#7a1315] to-[#cc2127] flex items-center justify-center text-xs text-white uppercase shadow-sm">
                                                    {user.email.charAt(0).toUpperCase()}
                                                </div>

                                                {user.email}

                                                <svg
                                                    className="-me-0.5 ms-1 h-4 w-4 opacity-70"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <div className="block px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                            Manage Account
                                        </div>
                                        <Dropdown.Link
                                            href={route('profile.edit')}
                                            className="font-medium"
                                        >
                                            Profile Settings
                                        </Dropdown.Link>
                                        <div className="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                                        <Dropdown.Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                            className="font-medium text-red-600 dark:text-red-400 focus:text-red-800 dark:focus:text-red-300"
                                        >
                                            Sign Out
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        {/* Mobile Menu Toggle */}
                        <div className="-me-2 flex items-center sm:hidden">
                            <button
                                onClick={() =>
                                    setShowingNavigationDropdown(
                                        (previousState) => !previousState,
                                    )
                                }
                                className="inline-flex items-center justify-center rounded-xl p-2.5 text-gray-500 dark:text-gray-400 transition-colors duration-300 ease-in-out hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-200 focus:bg-gray-100 dark:focus:bg-gray-800 focus:text-gray-700 dark:focus:text-gray-200 focus:outline-none"
                            >
                                <svg
                                    className="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        className={
                                            !showingNavigationDropdown
                                                ? 'inline-flex'
                                                : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={
                                            showingNavigationDropdown
                                                ? 'inline-flex'
                                                : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {/* Mobile Dropdown Menu */}
                <div
                    className={
                        (showingNavigationDropdown ? 'block' : 'hidden') +
                        ' sm:hidden border-t border-gray-200 dark:border-gray-800 absolute w-full bg-white dark:bg-gray-900 shadow-xl dark:shadow-2xl'
                    }
                >
                    <div className="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            href={route(dashboardRoute)}
                            active={dashboardActive}
                        >
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <div className="border-t border-gray-200 dark:border-gray-800 pb-1 pt-4 bg-gray-50 dark:bg-gray-800/50">
                        <div className="px-4 flex items-center gap-3 mb-3">
                            <div className="h-10 w-10 rounded-full bg-gradient-to-tr from-[#7a1315] to-[#cc2127] flex items-center justify-center text-sm font-bold text-white uppercase shadow-sm">
                                {user.email.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <div className="text-base font-bold text-gray-800 dark:text-gray-100">
                                    {user.email}
                                </div>
                                <div className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {user.email}
                                </div>
                            </div>
                        </div>

                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')}>
                                Profile Settings
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                method="post"
                                href={route('logout')}
                                as="button"
                                className="!text-red-600 dark:!text-red-400 hover:!bg-red-50 dark:hover:!bg-red-900/20"
                            >
                                Sign Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Page Header Area */}
            {header && (
                <header className="bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border-b border-gray-200 dark:border-gray-800">
                    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 flex items-center justify-between">
                        {header}
                    </div>
                </header>
            )}

            {/* Main Content Area */}
            <main className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {children}
            </main>
        </div>
    );
}
