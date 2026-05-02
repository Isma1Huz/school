import React from 'react';
import { useLanguageContext } from '@/providers/LanguageProvider';
import * as Select from '@radix-ui/react-select';
import { Globe, ChevronDown } from 'lucide-react';

const languages = [
    { code: 'en', label: 'English' },
    { code: 'sw', label: 'Swahili' },
];

export function LanguageSwitcher() {
    const { language, setLanguage } = useLanguageContext();

    return (
        <Select.Root value={language} onValueChange={setLanguage}>
            <Select.Trigger className="flex items-center gap-1 rounded-md px-2 py-1.5 text-sm text-gray-600 hover:bg-gray-100 focus:outline-none">
                <Globe className="h-4 w-4" />
                <Select.Value />
                <ChevronDown className="h-3 w-3" />
            </Select.Trigger>
            <Select.Portal>
                <Select.Content className="z-50 overflow-hidden rounded-md border bg-white shadow-md">
                    <Select.Viewport className="p-1">
                        {languages.map((lang) => (
                            <Select.Item
                                key={lang.code}
                                value={lang.code}
                                className="flex cursor-pointer select-none items-center rounded-sm px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none data-[highlighted]:bg-gray-100"
                            >
                                <Select.ItemText>{lang.label}</Select.ItemText>
                            </Select.Item>
                        ))}
                    </Select.Viewport>
                </Select.Content>
            </Select.Portal>
        </Select.Root>
    );
}
