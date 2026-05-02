import React from 'react';
import { cn } from '@/lib/utils';

interface StockLevelProps {
    current: number;
    max: number;
    threshold?: number;
    label?: string;
}

export function StockLevel({ current, max, threshold = 10, label }: StockLevelProps) {
    const pct = max > 0 ? Math.min(Math.round((current / max) * 100), 100) : 0;
    const isLow = current <= threshold;

    return (
        <div className="space-y-1">
            {label && <p className="text-xs text-gray-600">{label}</p>}
            <div className="flex items-center gap-2 text-sm">
                <span className={cn('font-medium', isLow ? 'text-red-600' : 'text-gray-900')}>{current}</span>
                <span className="text-gray-400">/ {max}</span>
                {isLow && <span className="rounded-full bg-red-100 px-1.5 py-0.5 text-xs text-red-700">Low</span>}
            </div>
            <div className="h-1.5 w-full rounded-full bg-gray-200">
                <div
                    className={cn('h-full rounded-full', isLow ? 'bg-red-500' : 'bg-green-500')}
                    style={{ width: `${pct}%` }}
                />
            </div>
        </div>
    );
}
