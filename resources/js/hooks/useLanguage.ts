import { useTranslation } from 'react-i18next';

export function useLanguage() {
    const { t, i18n } = useTranslation();

    return {
        t,
        currentLanguage: i18n.language,
        changeLanguage: (lang: string) => i18n.changeLanguage(lang),
        availableLanguages: [
            { code: 'en', label: 'English' },
            { code: 'sw', label: 'Swahili' },
        ],
    };
}
