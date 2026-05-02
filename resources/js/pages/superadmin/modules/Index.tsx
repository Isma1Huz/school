import React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import type { Module, PageProps } from '@/types';

interface ModulesIndexProps extends PageProps {
  modules: Module[];
}

export default function ModulesIndex() {
  const { modules } = usePage<ModulesIndexProps>().props;

  function handleToggle(id: number) {
    router.patch(`/super-admin/modules/${id}/toggle`);
  }

  return (
    <SuperAdminLayout>
      <Head title="Modules" />
      <PageHeader
        title="Modules"
        description="Manage system modules"
        breadcrumbs={[{ label: 'Modules' }]}
      />
      <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
              <tr>
                <th className="px-6 py-3 text-left">Name</th>
                <th className="px-6 py-3 text-left">Key</th>
                <th className="px-6 py-3 text-left">Core</th>
                <th className="px-6 py-3 text-left">Allowed Types</th>
                <th className="px-6 py-3 text-left">Status</th>
                <th className="px-6 py-3 text-left">Toggle</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {(modules ?? []).map((mod) => (
                <tr key={mod.id} className="hover:bg-gray-50">
                  <td className="px-6 py-3 font-medium text-gray-900">{mod.name}</td>
                  <td className="px-6 py-3 font-mono text-xs text-gray-500">{mod.key}</td>
                  <td className="px-6 py-3">
                    {mod.is_core && <StatusBadge status="core" />}
                  </td>
                  <td className="px-6 py-3">
                    <div className="flex flex-wrap gap-1">
                      {(mod.allowed_tenant_types ?? []).map((t) => (
                        <span key={t} className="rounded bg-blue-50 px-1.5 py-0.5 text-xs text-blue-700">{t}</span>
                      ))}
                    </div>
                  </td>
                  <td className="px-6 py-3"><StatusBadge status={mod.is_active ? 'active' : 'inactive'} /></td>
                  <td className="px-6 py-3">
                    <button
                      onClick={() => handleToggle(mod.id)}
                      className={`relative inline-flex h-5 w-9 items-center rounded-full transition-colors ${mod.is_active ? 'bg-green-500' : 'bg-gray-300'}`}
                    >
                      <span className={`inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform ${mod.is_active ? 'translate-x-4' : 'translate-x-1'}`} />
                    </button>
                  </td>
                </tr>
              ))}
              {(modules ?? []).length === 0 && (
                <tr><td colSpan={6} className="px-6 py-8 text-center text-gray-400">No modules found</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
