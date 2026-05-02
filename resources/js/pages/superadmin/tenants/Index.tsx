import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { Pagination } from '@/components/shared/Pagination';
import { ConfirmationDialog } from '@/components/shared/ConfirmationDialog';
import { Button } from '@/components/ui/button';
import { Plus, Eye, Edit, Trash2 } from 'lucide-react';
import { formatDate } from '@/lib/formatters';
import type { PageProps, PaginatedData } from '@/types';

interface TenantRow {
  id: number; name: string; slug: string; type: string; status: string;
  subscription_status: string; users_count: number; created_at: string;
}

interface TenantsIndexProps extends PageProps {
  tenants: PaginatedData<TenantRow>;
}

export default function TenantsIndex() {
  const { tenants } = usePage<TenantsIndexProps>().props;
  const [deleteTarget, setDeleteTarget] = useState<TenantRow | null>(null);

  function handlePageChange(page: number) {
    router.get('/super-admin/tenants', { page }, { preserveState: true, replace: true });
  }

  return (
    <SuperAdminLayout>
      <Head title="Tenants" />
      <PageHeader
        title="Tenants"
        description="Manage all tenants in the system"
        breadcrumbs={[{ label: 'Tenants' }]}
        actions={
          <Link href="/super-admin/tenants/create">
            <Button className="bg-[#800020] hover:bg-[#6b001b]">
              <Plus className="mr-2 h-4 w-4" /> New Tenant
            </Button>
          </Link>
        }
      />
      <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
              <tr>
                <th className="px-6 py-3 text-left">Name</th>
                <th className="px-6 py-3 text-left">Type</th>
                <th className="px-6 py-3 text-left">Status</th>
                <th className="px-6 py-3 text-left">Subscription</th>
                <th className="px-6 py-3 text-left">Users</th>
                <th className="px-6 py-3 text-left">Created</th>
                <th className="px-6 py-3 text-left">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {(tenants?.data ?? []).map((t) => (
                <tr key={t.id} className="hover:bg-gray-50">
                  <td className="px-6 py-3">
                    <div className="font-medium text-gray-900">{t.name}</div>
                    <div className="text-xs text-gray-400">{t.slug}</div>
                  </td>
                  <td className="px-6 py-3 capitalize text-gray-600">{t.type}</td>
                  <td className="px-6 py-3"><StatusBadge status={t.status} /></td>
                  <td className="px-6 py-3"><StatusBadge status={t.subscription_status} /></td>
                  <td className="px-6 py-3 text-gray-600">{t.users_count}</td>
                  <td className="px-6 py-3 text-gray-500">{formatDate(t.created_at)}</td>
                  <td className="px-6 py-3">
                    <div className="flex items-center gap-1">
                      <Link href={`/super-admin/tenants/${t.id}`}><Button variant="ghost" size="icon"><Eye className="h-4 w-4" /></Button></Link>
                      <Link href={`/super-admin/tenants/${t.id}/edit`}><Button variant="ghost" size="icon"><Edit className="h-4 w-4" /></Button></Link>
                      <Button variant="ghost" size="icon" onClick={() => setDeleteTarget(t)}><Trash2 className="h-4 w-4 text-red-500" /></Button>
                    </div>
                  </td>
                </tr>
              ))}
              {(tenants?.data ?? []).length === 0 && (
                <tr><td colSpan={7} className="px-6 py-8 text-center text-gray-400">No tenants found</td></tr>
              )}
            </tbody>
          </table>
        </div>
        <div className="px-6 py-4">
          <Pagination currentPage={tenants?.current_page ?? 1} lastPage={tenants?.last_page ?? 1} onPageChange={handlePageChange} />
        </div>
      </div>

      <ConfirmationDialog
        open={!!deleteTarget}
        onOpenChange={(open) => !open && setDeleteTarget(null)}
        title={`Delete ${deleteTarget?.name}?`}
        description="This will soft-delete the tenant. All data is preserved."
        confirmLabel="Delete"
        destructive
        onConfirm={() => {
          if (deleteTarget) {
            router.delete(`/super-admin/tenants/${deleteTarget.id}`, { onFinish: () => setDeleteTarget(null) });
          }
        }}
      />
    </SuperAdminLayout>
  );
}
