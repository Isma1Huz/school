import React, { useEffect } from 'react';
import { useFlash } from '@/hooks/useFlash';
import { cn } from '@/lib/utils';
import { CheckCircle, AlertCircle, Info, AlertTriangle, X } from 'lucide-react';

const variantMap = {
    success: { icon: CheckCircle,    bg: 'bg-green-50',  border: 'border-green-400', text: 'text-green-800' },
    error:   { icon: AlertCircle,    bg: 'bg-red-50',    border: 'border-red-400',   text: 'text-red-800' },
    info:    { icon: Info,           bg: 'bg-blue-50',   border: 'border-blue-400',  text: 'text-blue-800' },
    warning: { icon: AlertTriangle,  bg: 'bg-yellow-50', border: 'border-yellow-400',text: 'text-yellow-800' },
} as const;

export function FlashMessage() {
    const flash = useFlash();

    const entries = (Object.keys(variantMap) as Array<keyof typeof variantMap>).filter(
        (key) => !!flash[key],
    );

    if (entries.length === 0) return null;

    return (
        <div className="mb-4 space-y-2">
            {entries.map((type) => {
                const { icon: Icon, bg, border, text } = variantMap[type];
                return (
                    <div
                        key={type}
                        className={cn('flex items-start gap-3 rounded-md border p-4', bg, border, text)}
                    >
                        <Icon className="mt-0.5 h-5 w-5 shrink-0" />
                        <p className="text-sm">{flash[type]}</p>
                    </div>
                );
            })}
        </div>
    );
}
