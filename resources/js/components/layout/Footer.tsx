import React from 'react';
import { cn } from '@/lib/utils';

interface FooterProps {
    className?: string;
}

export function Footer({ className }: FooterProps) {
    return (
        <footer className={cn('border-t bg-white px-6 py-3 text-center text-xs text-gray-400', className)}>
            © {new Date().getFullYear()} Schoolzee — Powered by the multi-tenant platform.
        </footer>
    );
}
