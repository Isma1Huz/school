import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Bell } from 'lucide-react';
import * as Popover from '@radix-ui/react-popover';
import api from '@/lib/api';
import { timeAgo } from '@/lib/formatters';

interface NotificationItem {
    id: string;
    type: string;
    data: Record<string, unknown>;
    channel: string;
    created_at: string;
}

const POLL_INTERVAL_MS = 60_000; // 60 seconds

export function NotificationBell() {
    const [count, setCount] = useState(0);
    const [notifications, setNotifications] = useState<NotificationItem[]>([]);
    const [open, setOpen] = useState(false);
    const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

    const fetchUnread = useCallback(async () => {
        try {
            const { data } = await api.get<{ count: number; notifications: NotificationItem[] }>(
                '/notifications/unread',
            );
            setCount(data.count ?? 0);
            setNotifications(data.notifications ?? []);
        } catch {
            // Silently ignore — user is not in a tenant context or not logged in
        }
    }, []);

    useEffect(() => {
        fetchUnread();
        intervalRef.current = setInterval(fetchUnread, POLL_INTERVAL_MS);
        return () => {
            if (intervalRef.current) clearInterval(intervalRef.current);
        };
    }, [fetchUnread]);

    async function handleMarkAllRead() {
        try {
            await api.post('/notifications/read-all');
            setCount(0);
            setNotifications([]);
        } catch {
            // ignore
        }
    }

    async function handleMarkRead(id: string) {
        try {
            await api.post(`/notifications/${id}/read`);
            setNotifications((prev) => prev.filter((n) => n.id !== id));
            setCount((c) => Math.max(0, c - 1));
        } catch {
            // ignore
        }
    }

    return (
        <Popover.Root open={open} onOpenChange={setOpen}>
            <Popover.Trigger asChild>
                <button
                    className="relative rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                    aria-label="Notifications"
                >
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
                    className="z-50 w-96 rounded-md border bg-white shadow-lg"
                >
                    {/* Header */}
                    <div className="flex items-center justify-between border-b px-4 py-3">
                        <p className="text-sm font-semibold text-gray-900">Notifications</p>
                        {count > 0 && (
                            <button
                                onClick={handleMarkAllRead}
                                className="text-xs text-blue-600 hover:underline"
                            >
                                Mark all as read
                            </button>
                        )}
                    </div>

                    {/* List */}
                    <div className="max-h-80 overflow-y-auto divide-y divide-gray-100">
                        {notifications.length === 0 ? (
                            <p className="px-4 py-6 text-center text-sm text-gray-400">
                                No new notifications.
                            </p>
                        ) : (
                            notifications.map((n) => (
                                <div
                                    key={n.id}
                                    className="flex items-start gap-3 px-4 py-3 hover:bg-gray-50"
                                >
                                    <div className="mt-0.5 flex h-2 w-2 shrink-0 rounded-full bg-blue-500" />
                                    <div className="flex-1 min-w-0">
                                        <p className="truncate text-sm font-medium text-gray-900">
                                            {(n.data['title'] as string) ?? n.type}
                                        </p>
                                        {n.data['body'] && (
                                            <p className="mt-0.5 text-xs text-gray-500 line-clamp-2">
                                                {n.data['body'] as string}
                                            </p>
                                        )}
                                        <p className="mt-1 text-xs text-gray-400">{timeAgo(n.created_at)}</p>
                                    </div>
                                    <button
                                        onClick={() => handleMarkRead(n.id)}
                                        className="shrink-0 text-xs text-gray-400 hover:text-gray-600"
                                        title="Dismiss"
                                    >
                                        ✕
                                    </button>
                                </div>
                            ))
                        )}
                    </div>
                </Popover.Content>
            </Popover.Portal>
        </Popover.Root>
    );
}
