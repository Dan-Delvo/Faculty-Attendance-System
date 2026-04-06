import { Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    children,
    ...props
}) {
    return (
        <Link
            {...props}
            className={`flex w-full items-start border-l-4 py-2 pe-4 ps-3 ${active
                    ? 'border-[#7a1315] bg-red-50 text-[#7a1315] focus:border-[#cc2127] focus:bg-red-100 focus:text-[#a81a1e] dark:border-red-500 dark:bg-red-900/20 dark:text-red-300 dark:focus:border-red-400 dark:focus:bg-red-900/30 dark:focus:text-red-200'
                    : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 focus:border-gray-300 focus:bg-gray-50 focus:text-gray-800 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200 dark:focus:border-gray-600 dark:focus:bg-gray-700 dark:focus:text-gray-200'
                } text-base font-medium transition-all duration-300 ease-in-out focus:outline-none ${className}`}
        >
            {children}
        </Link>
    );
}
