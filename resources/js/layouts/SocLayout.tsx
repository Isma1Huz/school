import React, { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import {
  LayoutDashboard, Activity, Gauge, Shield,
  ChevronLeft, ChevronRight, LogOut, ChevronDown, ArrowLeft,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { FlashMessage } from '@/components/shared/FlashMessage';
import type { PageProps } from '@/types';

const navItems = [
  { href: '/soc',                 label: 'Dashboard',     icon: LayoutDashboard },
  { href: '/soc/activity-logs',   label: 'Activity Logs', icon: Activity },
  { href: '/soc/rate-limit-logs', label: 'Rate Limits',   icon: Gauge },
  { href: '/soc/ip-management',   label: 'IP Management', icon: Shield },
  { href: '/super-admin',         label: 'Super Admin',   icon: ArrowLeft },
];

interface SocLayoutProps {
  children: React.ReactNode;
  title?: string;
  actions?: React.ReactNode;
}

function SocSidebar({ collapsed, onToggle }: { collapsed: boolean; onToggle: () => void }) {
  const { url } = usePage();
  const isActive = (href: string) => {
    if (href === '/soc') return url === '/soc' || url === '/soc/';
    return url.startsWith(href);
  };

  return (
    <aside className={cn('flex flex-col bg-[#0f172a] text-white transition-all duration-300 shrink-0', collapsed ? 'w-[60px]' : 'w-[220px]')}>
      <div className="flex h-16 items-center justify-center border-b border-white/10 px-3">
        {!collapsed && <span className="text-sm font-bold tracking-wide text-white">SOC Center</span>}
        {collapsed && <Shield className="h-6 w-6 text-white" />}
      </div>
      <nav className="flex-1 overflow-y-auto py-4 space-y-1 px-2">
        {navItems.map(({ href, label, icon: Icon }) => (
          <Link key={href} href={href}
            className={cn('flex items-center gap-3 rounded-lg px-2 py-2.5 text-sm font-medium transition-colors', isActive(href) ? 'bg-[#800020] text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white', collapsed && 'justify-center')}
            title={collapsed ? label : undefined}>
            <Icon className="h-5 w-5 shrink-0" />
            {!collapsed && <span>{label}</span>}
          </Link>
        ))}
      </nav>
      <button onClick={onToggle} className="flex h-10 items-center justify-center border-t border-white/10 text-slate-400 hover:text-white">
        {collapsed ? <ChevronRight className="h-4 w-4" /> : <ChevronLeft className="h-4 w-4" />}
      </button>
    </aside>
  );
}

function SocHeader({ title, actions }: { title?: string; actions?: React.ReactNode }) {
  const { auth } = usePage<PageProps>().props;
  const [menuOpen, setMenuOpen] = useState(false);

  return (
    <header className="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-6 shadow-sm">
      <div className="flex items-center gap-4">
        {title && <h1 className="text-xl font-semibold text-gray-800">{title}</h1>}
        {actions && <div>{actions}</div>}
      </div>
      <div className="relative">
        <button onClick={() => setMenuOpen((o) => !o)} className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
          <div className="flex h-8 w-8 items-center justify-center rounded-full bg-[#0f172a] text-white text-xs font-bold">
            {auth?.user?.name?.charAt(0)?.toUpperCase() ?? 'S'}
          </div>
          <span className="hidden md:block font-medium">{auth?.user?.name ?? 'SOC'}</span>
          <ChevronDown className="h-4 w-4 text-gray-400" />
        </button>
        {menuOpen && (
          <>
            <div className="fixed inset-0 z-10" onClick={() => setMenuOpen(false)} />
            <div className="absolute right-0 top-full z-20 mt-1 w-48 rounded-lg border bg-white shadow-lg py-1">
              <button onClick={() => router.post('/logout')} className="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                <LogOut className="h-4 w-4" /> Logout
              </button>
            </div>
          </>
        )}
      </div>
    </header>
  );
}

export default function SocLayout({ children, title, actions }: SocLayoutProps) {
  const [collapsed, setCollapsed] = useState(false);
  return (
    <div className="flex h-screen overflow-hidden bg-gray-50">
      <SocSidebar collapsed={collapsed} onToggle={() => setCollapsed((c) => !c)} />
      <div className="flex flex-1 flex-col overflow-hidden">
        <SocHeader title={title} actions={actions} />
        <main className="flex-1 overflow-y-auto p-6">
          <FlashMessage />
          {children}
        </main>
      </div>
    </div>
  );
}
