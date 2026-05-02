import React, { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { Pagination } from '@/components/shared/Pagination';
import { Search } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/formatters';
import type { PageProps, PaginatedData } from '@/types';

interface UserRow {
  id: number; name: string; email: string; user_type: string;
  tenant_name: string | null; is_active: boolean; last_login_at: string | null;
}

interface UsersIndexProps extends PageProps {
  users: PaginatedData<UserRow>;
  search: string;
}

export default function UsersIndex() {
  const { users, search: initialSearch } = usePage<UsersIndexProps>().props;
  const [searchValue, setSearchValue] = useState(initialSearch ?? '');

  function handleSearch(e: React.FormEvent) {
    e.preventDefault();
    router.get('/super-admin/users', { search: searchValue }, { preserveState: true, replace: true });
  }

  function handlePageChange(page: number) {
    router.get('/super-admin/users', { search: initialSearch, page }, { preserveState: true, replace: true });
  }

  return (
    <SuperAdminLayout>
      <Head title="Users" />
      <PageHeader title="Users" description="All users across all tenants" breadcrumbs={[{ label: 'Users' }]} />

      <form onSubmit={handleSearch} className="mb-4 flex gap-2">
        <input
          type="text"
          value={searchValue}
          onChange={(e) => setSearchValue(e.target.value)}
          placeholder="Search by name or email…"
          className="h-9 flex-1 max-w-sm rounded-md border border-gray-300 px-3 text-sm focus:border-[#800020] focus:outline-none focus:ring-1 focus:ring-[#800020]"
        />
        <Button type="submit" size="sm" className="h-9 bg-[#800020] hover:bg-[#6b001b]">
          <Search className="mr-1.5 h-3.5 w-3.5" /> Search
        </Button>
      </form>

      <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
              <tr>
                <th className="px-6 py-3 text-left">Name</th>
                <th className="px-6 py-3 text-left">Email</th>
                <th className="px-6 py-3 text-left">Type</th>
                <th className="px-6 py-3 text-left">Tenant</th>
                <th className="px-6 py-3 text-left">Active</th>
                <th className="px-6 py-3 text-left">Last Login</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {(users?.data ?? []).map((u) => (
                <tr key={u.id} className="hover:bg-gray-50">
                  <td className="px-6 py-3 font-medium text-gray-900">{u.name}</td>
                  <td className="px-6 py-3 text-gray-500">{u.email}</td>
                  <td className="px-6 py-3"><StatusBadge status={u.user_type} /></td>
                  <td className="px-6 py-3 text-gray-500">{u.tenant_name ?? '—'}</td>
                  <td className="px-6 py-3"><StatusBadge status={u.is_active ? 'active' : 'inactive'} /></td>
                  <td className="px-6 py-3 text-gray-500">{u.last_login_at ? formatDate(u.last_login_at) : '—'}</td>
                </tr>
              ))}
              {(users?.data ?? []).length === 0 && (
                <tr><td colSpan={6} className="px-6 py-8 text-center text-gray-400">No users found</td></tr>
              )}
            </tbody>
          </table>
        </div>
        <div className="px-6 py-4">
          <Pagination currentPage={users?.current_page ?? 1} lastPage={users?.last_page ?? 1} onPageChange={handlePageChange} />
        </div>
      </div>
    </SuperAdminLayout>
  );
}
