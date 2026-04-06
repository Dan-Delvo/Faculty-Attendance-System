export default function PrimaryButton({
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            className={
                `inline-flex items-center rounded-xl border border-transparent bg-gradient-to-r from-[#7a1315] to-[#cc2127] px-4 py-2 text-xs font-bold uppercase tracking-widest text-white shadow-md shadow-red-900/20 transition-all duration-300 ease-in-out hover:scale-105 hover:shadow-lg hover:shadow-red-900/30 focus:outline-none focus:ring-2 focus:ring-[#7a1315] focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-900 ${disabled && 'opacity-25 pointer-events-none'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
