import React from 'react';
import { cn } from '@/lib/utils';
import { formatBytes, formatPercent } from '@/lib/formatters';

interface UsageMeterProps {
    label: string;
    used: number;
    total: number;
    formatValue?: (value: number) => string;
    className?: string;
}

export function UsageMeter({ label, used, total, formatValue, className }: UsageMeterProps) {
    const pct = total > 0 ? Math.min(Math.round((used / total) * 100), 100) : 0;
    const isWarning = pct >= 75;
    const isDanger  = pct >= 90;

    const display = formatValue ? formatValue(used) : `${used} / ${total}`;

    return (
        <div className={cn('space-y-1', className)}>
            <div className="flex justify-between text-sm">
                <span className="text-gray-600">{label}</span>
                <span className={cn('font-medium', isDanger ? 'text-red-600' : isWarning ? 'text-yellow-600' : 'text-gray-900')}>
                    {display} ({pct}%)
                </span>
            </div>
            <div className="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                <div
                    className={cn(
                        'h-full rounded-full transition-all',
                        isDanger  ? 'bg-red-500'    :
                        isWarning ? 'bg-yellow-500' : 'bg-primary',
                    )}
                    style={{ width: `${pct}%` }}
                />
            </div>
        </div>
    );
}
