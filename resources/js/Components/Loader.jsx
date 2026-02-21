import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { Mosaic } from 'react-loading-indicators';

export default function Loader() {
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        const removeStartListener = router.on('start', () => {
            setIsLoading(true);
        });

        const removeFinishListener = router.on('finish', () => {
            setIsLoading(false);
        });

        return () => {
            removeStartListener();
            removeFinishListener();
        };
    }, []);

    if (!isLoading) return null;

    return (
        <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-white/70 dark:bg-gray-900/70 backdrop-blur-md transition-all duration-300">
            <Mosaic color="#cc2127" size="medium" text="Please wait..." textColor="#cc2127" />
        </div>
    );
}
