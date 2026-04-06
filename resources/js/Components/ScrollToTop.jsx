import { useState, useEffect } from 'react';

/**
 * Floating "Back to Top" button.
 *
 * Props
 * ─────────────────────────────────────────────
 *  threshold  : number  – scroll distance (px) before the button appears (default 300)
 *  className  : string  – extra wrapper classes
 */
export default function ScrollToTop({ threshold = 300, className = '' }) {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const onScroll = () => setVisible(window.scrollY > threshold);
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, [threshold]);

    const scrollUp = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    return (
        <button
            id="scroll-to-top"
            onClick={scrollUp}
            aria-label="Scroll to top"
            className={`
                fixed bottom-6 right-6 z-50
                flex h-11 w-11 items-center justify-center
                rounded-full shadow-lg
                bg-gray-900 dark:bg-white
                text-white dark:text-gray-900
                transition-all duration-300 ease-in-out
                hover:scale-110 hover:shadow-xl
                active:scale-95
                ${visible
                    ? 'translate-y-0 opacity-100 pointer-events-auto'
                    : 'translate-y-4 opacity-0 pointer-events-none'
                }
                ${className}
            `}
        >
            <svg
                className="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                strokeWidth={2.5}
                stroke="currentColor"
            >
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    d="M4.5 15.75l7.5-7.5 7.5 7.5"
                />
            </svg>
        </button>
    );
}
