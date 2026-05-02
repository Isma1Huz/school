import React, { createContext, useContext, useEffect, useState } from 'react';
import { useTenant } from '@/hooks/useTenant';

type Theme = 'light' | 'dark' | 'system';

interface ThemeContextValue {
    theme: Theme;
    setTheme: (theme: Theme) => void;
    primaryColor: string;
    secondaryColor: string;
}

const ThemeContext = createContext<ThemeContextValue>({
    theme: 'light',
    setTheme: () => undefined,
    primaryColor: '#800020',
    secondaryColor: '#FFD700',
});

export function ThemeProvider({ children }: { children: React.ReactNode }) {
    const tenant = useTenant();
    const [theme, setThemeState] = useState<Theme>(
        (localStorage.getItem('theme') as Theme) ?? 'light',
    );

    const primaryColor = tenant?.primary_color ?? '#800020';
    const secondaryColor = tenant?.secondary_color ?? '#FFD700';

    function setTheme(t: Theme) {
        setThemeState(t);
        localStorage.setItem('theme', t);
    }

    useEffect(() => {
        const root = document.documentElement;
        root.classList.remove('light', 'dark');

        if (theme === 'system') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            root.classList.add(prefersDark ? 'dark' : 'light');
        } else {
            root.classList.add(theme);
        }
    }, [theme]);

    // Inject tenant brand colours as CSS custom properties
    useEffect(() => {
        const root = document.documentElement;
        root.style.setProperty('--tenant-primary', primaryColor);
        root.style.setProperty('--tenant-secondary', secondaryColor);
    }, [primaryColor, secondaryColor]);

    return (
        <ThemeContext.Provider value={{ theme, setTheme, primaryColor, secondaryColor }}>
            {children}
        </ThemeContext.Provider>
    );
}

export function useTheme(): ThemeContextValue {
    return useContext(ThemeContext);
}
