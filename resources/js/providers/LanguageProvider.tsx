import React, { createContext, useContext, useEffect, useState } from 'react';
import i18n from 'i18next';
import { I18nextProvider, initReactI18next } from 'react-i18next';
import en from '@/lang/en.json';
import sw from '@/lang/sw.json';

const safeGetStorage = (key: string): string | null => {
    if (typeof localStorage === 'undefined') return null;
    try {
        return localStorage.getItem(key);
    } catch {
        return null;
    }
};

i18n.use(initReactI18next).init({
    resources: {
        en: { translation: en },
        sw: { translation: sw },
    },
    lng: safeGetStorage('language') ?? 'en',
    fallbackLng: 'en',
    interpolation: { escapeValue: false },
});

interface LanguageContextValue {
    language: string;
    setLanguage: (lang: string) => void;
}

const LanguageContext = createContext<LanguageContextValue>({
    language: 'en',
    setLanguage: () => undefined,
});

export function LanguageProvider({ children }: { children: React.ReactNode }) {
    const [language, setLanguageState] = useState<string>(() => safeGetStorage('language') ?? 'en');

    function setLanguage(lang: string) {
        setLanguageState(lang);
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('language', lang);
        }
        void i18n.changeLanguage(lang);
    }

    useEffect(() => {
        void i18n.changeLanguage(language);
    }, [language]);

    return (
        <LanguageContext.Provider value={{ language, setLanguage }}>
            <I18nextProvider i18n={i18n}>{children}</I18nextProvider>
        </LanguageContext.Provider>
    );
}

export function useLanguageContext(): LanguageContextValue {
    return useContext(LanguageContext);
}
