import { format, parseISO, formatDistanceToNow } from 'date-fns';

export function formatDate(date: string | Date | null | undefined, pattern = 'dd MMM yyyy'): string {
    if (!date) return '—';
    const d = typeof date === 'string' ? parseISO(date) : date;
    return format(d, pattern);
}

export function formatDateTime(date: string | Date | null | undefined): string {
    return formatDate(date, 'dd MMM yyyy, HH:mm');
}

export function timeAgo(date: string | Date | null | undefined): string {
    if (!date) return '—';
    const d = typeof date === 'string' ? parseISO(date) : date;
    return formatDistanceToNow(d, { addSuffix: true });
}

export function formatCurrency(
    amount: number | string | null | undefined,
    currency = 'KES',
    locale = 'en-KE',
): string {
    if (amount === null || amount === undefined) return '—';
    const num = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat(locale, { style: 'currency', currency }).format(num);
}

export function formatNumber(value: number | null | undefined, decimals = 0): string {
    if (value === null || value === undefined) return '—';
    return new Intl.NumberFormat().format(Number(value.toFixed(decimals)));
}

export function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${units[i]}`;
}

export function formatPercent(value: number, total: number): string {
    if (total === 0) return '0%';
    return `${Math.round((value / total) * 100)}%`;
}
