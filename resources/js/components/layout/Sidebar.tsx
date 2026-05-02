import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    LayoutDashboard, GraduationCap, DollarSign, Package,
    Users, MessageSquare, Settings, ChevronDown, ChevronRight,
    X, Menu,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { buildNavigation } from '@/config/navigation';
import { useModuleAccess } from '@/hooks/useModuleAccess';
import { usePermissions } from '@/hooks/usePermissions';
import { useTenant } from '@/hooks/useTenant';
import type { NavigationItem } from '@/types';

const iconMap: Record<string, React.ElementType> = {
    LayoutDashboard, GraduationCap, DollarSign, Package,
    Users, MessageSquare, Settings,
};

function NavIcon({ name }: { name?: string }) {
    const Icon = name ? iconMap[name] : null;
    return Icon ? <Icon className="h-5 w-5 shrink-0" /> : <span className="h-5 w-5" />;
}

interface SidebarItemProps {
    item: NavigationItem;
    currentUrl: string;
    depth?: number;
}

function SidebarItem({ item, currentUrl, depth = 0 }: SidebarItemProps) {
    const hasChildren = item.children && item.children.length > 0;
    const isActive = item.href ? currentUrl.startsWith(item.href) : false;
    const [open, setOpen] = useState(isActive);

    if (hasChildren) {
        return (
            <div>
                <button
                    onClick={() => setOpen((v) => !v)}
                    className={cn(
                        'flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        'text-gray-300 hover:bg-gray-700 hover:text-white',
                        depth > 0 && 'pl-8',
                    )}
                >
                    <NavIcon name={item.icon} />
                    <span className="flex-1 text-left">{item.name}</span>
                    {open ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
                </button>
                {open && (
                    <div className="ml-4 mt-1 space-y-1">
                        {item.children!.map((child) => (
                            <SidebarItem key={child.name} item={child} currentUrl={currentUrl} depth={depth + 1} />
                        ))}
                    </div>
                )}
            </div>
        );
    }

    return (
        <Link
            href={item.href ?? '#'}
            className={cn(
                'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                isActive
                    ? 'bg-primary text-white'
                    : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                depth > 0 && 'pl-8',
            )}
        >
            <NavIcon name={item.icon} />
            <span>{item.name}</span>
            {item.badge && (
                <span className="ml-auto rounded-full bg-primary px-2 py-0.5 text-xs text-white">
                    {item.badge}
                </span>
            )}
        </Link>
    );
}

interface SidebarProps {
    isOpen: boolean;
    isMobileOpen: boolean;
    onClose: () => void;
}

export function Sidebar({ isOpen, isMobileOpen, onClose }: SidebarProps) {
    const tenant = useTenant();
    const { enabledModules } = useModuleAccess();
    const { permissions } = usePermissions();
    const { url } = usePage();

    const navItems = buildNavigation(enabledModules, permissions);

    const inner = (
        <div className="flex h-full flex-col bg-gray-900 text-white">
            {/* Logo / Tenant name */}
            <div className="flex h-16 items-center justify-between px-4">
                <Link href="/dashboard" className="flex items-center gap-2">
                    {tenant?.logo ? (
                        <img src={tenant.logo} alt={tenant.name} className="h-8 w-8 rounded object-cover" />
                    ) : (
                        <div
                            className="flex h-8 w-8 items-center justify-center rounded text-sm font-bold text-white"
                            style={{ backgroundColor: tenant?.primary_color ?? '#800020' }}
                        >
                            {tenant?.name?.charAt(0) ?? 'S'}
                        </div>
                    )}
                    <span className="text-lg font-semibold">{tenant?.name ?? 'Schoolzee'}</span>
                </Link>
                <button
                    onClick={onClose}
                    className="rounded p-1 text-gray-400 hover:text-white lg:hidden"
                >
                    <X className="h-5 w-5" />
                </button>
            </div>

            {/* Navigation */}
            <nav className="flex-1 overflow-y-auto px-3 py-4">
                <div className="space-y-1">
                    {navItems.map((item) => (
                        <SidebarItem key={item.name} item={item} currentUrl={url} />
                    ))}
                </div>
            </nav>
        </div>
    );

    return (
        <>
            {/* Desktop */}
            <aside
                className={cn(
                    'hidden lg:flex flex-col fixed inset-y-0 left-0 z-40 w-64 transition-transform duration-200',
                    !isOpen && '-translate-x-full',
                )}
            >
                {inner}
            </aside>

            {/* Mobile overlay */}
            {isMobileOpen && (
                <>
                    <div
                        className="fixed inset-0 z-40 bg-black/60 lg:hidden"
                        onClick={onClose}
                    />
                    <aside className="fixed inset-y-0 left-0 z-50 w-64 lg:hidden">{inner}</aside>
                </>
            )}
        </>
    );
}
