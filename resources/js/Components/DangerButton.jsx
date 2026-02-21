export default function DangerButton({
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            className={
                `inline-flex items-center rounded-xl border border-transparent bg-red-600 px-4 py-2 text-xs font-bold uppercase tracking-widest text-white shadow-md transition-all duration-300 ease-in-out hover:bg-red-500 hover:scale-105 hover:shadow-lg hover:shadow-red-600/20 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700 active:scale-95 dark:focus:ring-offset-gray-900 ${disabled && 'opacity-25 pointer-events-none'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
