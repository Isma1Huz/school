import React, { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { LogOut, User, ChevronDown } from 'lucide-react';
import type { PageProps } from '@/types';

interface SuperAdminHeaderProps {
  title?: string;
  actions?: React.ReactNode;
}

export function SuperAdminHeader({ title, actions }: SuperAdminHeaderProps) {
  const { auth } = usePage<PageProps>().props;
  const [menuOpen, setMenuOpen] = useState(false);

  function handleLogout() {
    router.post('/logout');
  }

  return (
    <header className="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-6 shadow-sm">
      <div className="flex items-center gap-4">
        {title && (
          <h1 className="text-xl font-semibold text-gray-800">{title}</h1>
        )}
        {actions && <div className="flex items-center gap-2">{actions}</div>}
      </div>

      <div className="relative">
        <button
          onClick={() => setMenuOpen((o) => !o)}
          className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
        >
          <div className="flex h-8 w-8 items-center justify-center rounded-full bg-[#800020] text-white text-xs font-bold">
            {auth?.user?.name?.charAt(0)?.toUpperCase() ?? 'S'}
          </div>
          <span className="hidden md:block font-medium">{auth?.user?.name ?? 'Admin'}</span>
          <ChevronDown className="h-4 w-4 text-gray-400" />
        </button>

        {menuOpen && (
          <>
            <div className="fixed inset-0 z-10" onClick={() => setMenuOpen(false)} />
            <div className="absolute right-0 top-full z-20 mt-1 w-48 rounded-lg border border-gray-200 bg-white shadow-lg py-1">
              <div className="px-4 py-2 border-b border-gray-100">
                <p className="text-xs text-gray-500">Signed in as</p>
                <p className="text-sm font-medium text-gray-800 truncate">{auth?.user?.email}</p>
              </div>
              <Link
                href="/profile"
                className="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                onClick={() => setMenuOpen(false)}
              >
                <User className="h-4 w-4" />
                Profile
              </Link>
              <button
                onClick={handleLogout}
                className="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50"
              >
                <LogOut className="h-4 w-4" />
                Logout
              </button>
            </div>
          </>
        )}
      </div>
    </header>
  );
}
