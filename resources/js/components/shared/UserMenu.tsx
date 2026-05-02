import React from 'react';
import { Link, router } from '@inertiajs/react';
import { LogOut, User, Settings } from 'lucide-react';
import * as DropdownMenu from '@radix-ui/react-dropdown-menu';
import { initials } from '@/lib/utils';
import type { AuthUser } from '@/types';

interface UserMenuProps {
    user: AuthUser;
}

export function UserMenu({ user }: UserMenuProps) {
    function handleLogout() {
        router.post('/logout');
    }

    return (
        <DropdownMenu.Root>
            <DropdownMenu.Trigger asChild>
                <button className="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    {user.avatar ? (
                        <img src={user.avatar} alt={user.name} className="h-9 w-9 rounded-full object-cover" />
                    ) : (
                        initials(user.name)
                    )}
                </button>
            </DropdownMenu.Trigger>

            <DropdownMenu.Portal>
                <DropdownMenu.Content
                    align="end"
                    sideOffset={8}
                    className="z-50 min-w-48 rounded-md border bg-white p-1 shadow-lg"
                >
                    <div className="px-3 py-2">
                        <p className="text-sm font-medium text-gray-900">{user.name}</p>
                        <p className="truncate text-xs text-gray-500">{user.email}</p>
                    </div>
                    <DropdownMenu.Separator className="my-1 h-px bg-gray-100" />

                    <DropdownMenu.Item asChild>
                        <Link
                            href="/profile"
                            className="flex cursor-pointer items-center gap-2 rounded-sm px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none"
                        >
                            <User className="h-4 w-4" />
                            Profile
                        </Link>
                    </DropdownMenu.Item>
                    <DropdownMenu.Item asChild>
                        <Link
                            href="/settings"
                            className="flex cursor-pointer items-center gap-2 rounded-sm px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none"
                        >
                            <Settings className="h-4 w-4" />
                            Settings
                        </Link>
                    </DropdownMenu.Item>

                    <DropdownMenu.Separator className="my-1 h-px bg-gray-100" />

                    <DropdownMenu.Item
                        onSelect={handleLogout}
                        className="flex cursor-pointer items-center gap-2 rounded-sm px-3 py-2 text-sm text-red-600 hover:bg-red-50 focus:outline-none"
                    >
                        <LogOut className="h-4 w-4" />
                        Sign Out
                    </DropdownMenu.Item>
                </DropdownMenu.Content>
            </DropdownMenu.Portal>
        </DropdownMenu.Root>
    );
}
