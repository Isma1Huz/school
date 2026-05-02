import React from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
  LayoutDashboard, Building2, Package, CreditCard,
  Users, Shield, ChevronLeft, ChevronRight,
} from 'lucide-react';
import { cn } from '@/lib/utils';

const navItems = [
  { href: '/super-admin',         label: 'Dashboard', icon: LayoutDashboard },
  { href: '/super-admin/tenants', label: 'Tenants',   icon: Building2 },
  { href: '/super-admin/modules', label: 'Modules',   icon: Package },
  { href: '/super-admin/plans',   label: 'Plans',     icon: CreditCard },
  { href: '/super-admin/users',   label: 'Users',     icon: Users },
  { href: '/soc',                 label: 'SOC',       icon: Shield },
];

interface SuperAdminSidebarProps {
  collapsed: boolean;
  onToggle: () => void;
}

export function SuperAdminSidebar({ collapsed, onToggle }: SuperAdminSidebarProps) {
  const { url } = usePage();

  const isActive = (href: string) => {
    if (href === '/super-admin') return url === '/super-admin' || url === '/super-admin/';
    return url.startsWith(href);
  };

  return (
    <aside
      className={cn(
        'flex flex-col bg-[#1e293b] text-white transition-all duration-300 ease-in-out shrink-0',
        collapsed ? 'w-[60px]' : 'w-[220px]',
      )}
    >
      {/* Logo */}
      <div className="flex h-16 items-center justify-center border-b border-white/10 px-3">
        {!collapsed && (
          <span className="text-sm font-bold tracking-wide text-white truncate">
            Super Admin
          </span>
        )}
        {collapsed && <Shield className="h-6 w-6 text-white" />}
      </div>

      {/* Navigation */}
      <nav className="flex-1 overflow-y-auto py-4 space-y-1 px-2">
        {navItems.map(({ href, label, icon: Icon }) => (
          <Link
            key={href}
            href={href}
            className={cn(
              'flex items-center gap-3 rounded-lg px-2 py-2.5 text-sm font-medium transition-colors',
              isActive(href)
                ? 'bg-[#800020] text-white'
                : 'text-slate-300 hover:bg-white/10 hover:text-white',
              collapsed && 'justify-center',
            )}
            title={collapsed ? label : undefined}
          >
            <Icon className="h-5 w-5 shrink-0" />
            {!collapsed && <span>{label}</span>}
          </Link>
        ))}
      </nav>

      {/* Toggle */}
      <button
        onClick={onToggle}
        className="flex h-10 items-center justify-center border-t border-white/10 text-slate-400 hover:text-white transition-colors"
      >
        {collapsed ? <ChevronRight className="h-4 w-4" /> : <ChevronLeft className="h-4 w-4" />}
      </button>
    </aside>
  );
}
