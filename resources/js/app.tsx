import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from '@/hooks/use-appearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: (name) => {
        const pages = import.meta.glob<{ default: React.ComponentType }>('./pages/**/*.tsx', { eager: true });

        // Direct match (exact case)
        if (pages[`./pages/${name}.tsx`]) {
            return pages[`./pages/${name}.tsx`];
        }

        // Case-insensitive fallback (controllers use PascalCase, files use lowercase dirs)
        const needle = `./pages/${name}.tsx`.toLowerCase();
        const match = Object.entries(pages).find(([key]) => key.toLowerCase() === needle);
        if (match) return match[1];

        throw new Error(`Page not found: ${name}`);
    },

    strictMode: true,

    setup({ el, App, props }) {
        createRoot(el as any).render(
            // <TooltipProvider delayDuration={0}>
                <App {...props} />
            // </TooltipProvider>
        );
    },

    progress: {
        color: '#4B5563',
    },
});

initializeTheme();