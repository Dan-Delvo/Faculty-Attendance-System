export default function SecondaryButton({
    type = 'button',
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            type={type}
            className={
                `inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-700 shadow-sm transition-all duration-300 ease-in-out hover:bg-gray-50 hover:scale-105 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#7a1315] focus:ring-offset-2 disabled:opacity-25 active:scale-95 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-offset-gray-900 ${disabled && 'opacity-25 pointer-events-none'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
