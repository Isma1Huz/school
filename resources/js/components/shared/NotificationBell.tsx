import React, { useState } from 'react';
import { Bell } from 'lucide-react';
import * as Popover from '@radix-ui/react-popover';

export function NotificationBell() {
    const [count] = useState(0);

    return (
        <Popover.Root>
            <Popover.Trigger asChild>
                <button className="relative rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                    <Bell className="h-5 w-5" />
                    {count > 0 && (
                        <span className="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                            {count > 9 ? '9+' : count}
                        </span>
                    )}
                </button>
            </Popover.Trigger>
            <Popover.Portal>
                <Popover.Content
                    align="end"
                    sideOffset={8}
                    className="z-50 w-80 rounded-md border bg-white p-4 shadow-lg"
                >
                    <p className="text-sm font-medium text-gray-900">Notifications</p>
                    <p className="mt-2 text-sm text-gray-500">No new notifications.</p>
                </Popover.Content>
            </Popover.Portal>
        </Popover.Root>
    );
}
