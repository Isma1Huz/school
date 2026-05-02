import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { TenantInfoCard } from './TenantInfoCard';
import { TenantModules } from './TenantModules';
import type { Tenant, PageProps } from '@/types';

interface TenantModuleEntry {
  id: number; name: string; key: string; is_active: boolean;
}

interface TenantUser {
  id: number; name: string; email: string; is_active: boolean;
}

interface ShowTenantProps extends PageProps {
  tenant: Tenant;
  tenantModules: TenantModuleEntry[];
  tenantUsers: TenantUser[];
}

export default function ShowTenant() {
  const { tenant, tenantModules, tenantUsers } = usePage<ShowTenantProps>().props;

  return (
    <SuperAdminLayout>
      <Head title={tenant.name} />
      <PageHeader
        title={tenant.name}
        breadcrumbs={[{ label: 'Tenants', href: '/super-admin/tenants' }, { label: tenant.name }]}
      />
      <div className="space-y-6">
        <TenantInfoCard tenant={tenant} />
        <TenantModules tenantId={tenant.id} modules={tenantModules ?? []} />

        {/* Users table */}
        <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
          <div className="border-b border-gray-100 px-6 py-4">
            <h3 className="text-base font-semibold text-gray-900">Users ({(tenantUsers ?? []).length})</h3>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
                <tr>
                  <th className="px-6 py-3 text-left">Name</th>
                  <th className="px-6 py-3 text-left">Email</th>
                  <th className="px-6 py-3 text-left">Active</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {(tenantUsers ?? []).map((u) => (
                  <tr key={u.id} className="hover:bg-gray-50">
                    <td className="px-6 py-3 font-medium text-gray-900">{u.name}</td>
                    <td className="px-6 py-3 text-gray-500">{u.email}</td>
                    <td className="px-6 py-3">
                      <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${u.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}`}>
                        {u.is_active ? 'Yes' : 'No'}
                      </span>
                    </td>
                  </tr>
                ))}
                {(tenantUsers ?? []).length === 0 && (
                  <tr><td colSpan={3} className="px-6 py-8 text-center text-gray-400">No users</td></tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
