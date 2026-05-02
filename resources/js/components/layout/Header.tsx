import React from 'react';
import { usePage } from '@inertiajs/react';
import { Menu, Bell } from 'lucide-react';
import { cn } from '@/lib/utils';
import { UserMenu } from '@/components/shared/UserMenu';
import { NotificationBell } from '@/components/shared/NotificationBell';
import { LanguageSwitcher } from '@/components/shared/LanguageSwitcher';
import type { PageProps } from '@/types';

interface HeaderProps {
    onMenuToggle: () => void;
    className?: string;
}

export function Header({ onMenuToggle, className }: HeaderProps) {
    const { auth } = usePage<PageProps>().props;

    return (
        <header
            className={cn(
                'sticky top-0 z-30 flex h-16 items-center gap-4 border-b bg-white px-4 shadow-sm',
                className,
            )}
        >
            <button
                onClick={onMenuToggle}
                className="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                aria-label="Toggle navigation"
            >
                <Menu className="h-5 w-5" />
            </button>

            <div className="flex-1" />

            <div className="flex items-center gap-3">
                <LanguageSwitcher />
                <NotificationBell />
                {auth?.user && <UserMenu user={auth.user} />}
            </div>
        </header>
    );
}
