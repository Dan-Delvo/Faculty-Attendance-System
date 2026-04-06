export default function Checkbox({ className = '', ...props }) {
    return (
        <input
            {...props}
            type="checkbox"
            className={
                'rounded border-gray-300 text-[#7a1315] shadow-sm focus:ring-[#7a1315] transition-all duration-300 cursor-pointer dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-red-600 dark:focus:ring-offset-gray-900 ' +
                className
            }
        />
    );
}
