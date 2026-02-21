import { Head } from '@inertiajs/react';

export default function LandingLayout({ children, title }) {
    return (
        <>
            {title && <Head title={title} />}
            <div className="flex min-h-screen flex-col bg-gray-50 text-gray-900 selection:bg-[#7a1315] selection:text-white dark:bg-gray-900 dark:text-white font-sans overflow-x-hidden relative">

                {/* Decorative Background */}
                <div className="absolute inset-0 overflow-hidden pointer-events-none z-0">
                    <div className="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] opacity-10 blur-3xl rounded-full bg-gradient-to-br from-[#7a1315] to-transparent"></div>
                    <div className="absolute top-[20%] right-[-10%] w-[60%] h-[60%] opacity-5 blur-3xl rounded-full bg-gradient-to-l from-yellow-500 to-transparent"></div>
                    <div className="absolute bottom-[-10%] right-[10%] w-[40%] h-[40%] opacity-10 blur-3xl rounded-full bg-gradient-to-t from-[#cc2127] to-transparent"></div>
                </div>

                {/* Main Content Area */}
                <main className="relative z-10 flex flex-grow flex-col items-center justify-center p-6">
                    {children}
                </main>

                {/* Footer pinned to bottom */}
                <footer className="relative z-20 w-full p-6 text-center shrink-0">
                    <p className="text-sm font-medium text-gray-500 dark:text-gray-500">
                        &copy; {new Date().getFullYear()} Polytechnic University of the Philippines Taguig Campus. All rights reserved.
                    </p>
                </footer>
            </div>
        </>
    );
}
