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
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50 backdrop-blur-sm">
            <Mosaic color="#E10600" size="medium" text="Loading..." textColor="#E10600" />
        </div>
    );
}
