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
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-50 backdrop-blur-sm">
            <Mosaic color="#316ccc" size="medium" text="Loading" textColor="316ccc" />
        </div>
    );
}
