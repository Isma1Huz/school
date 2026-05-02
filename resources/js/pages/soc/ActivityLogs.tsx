import React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import SocLayout from '@/layouts/SocLayout';
import { FilterBar } from '@/components/shared/FilterBar';
import { Pagination } from '@/components/shared/Pagination';
import { formatDate } from '@/lib/formatters';
import type { PageProps, PaginatedData, ActivityLogEntry } from '@/types';

interface ActivityLogsProps extends PageProps {
  logs: PaginatedData<ActivityLogEntry>;
  tenants: Array<{ id: number; name: string }>;
  filters: Record<string, string>;
}

export default function ActivityLogs() {
  const { logs, tenants, filters } = usePage<ActivityLogsProps>().props;

  const tenantOptions = (tenants ?? []).map((t) => ({ value: String(t.id), label: t.name }));

  const filterFields = [
    { key: 'tenant_id', label: 'Tenant', type: 'select' as const, options: tenantOptions },
    { key: 'event', label: 'Event', type: 'text' as const },
    { key: 'causer_id', label: 'Causer ID', type: 'text' as const },
    { key: 'date_from', label: 'From', type: 'text' as const },
    { key: 'date_to', label: 'To', type: 'text' as const },
  ];

  function handleFilter(f: Record<string, string>) {
    router.get('/soc/activity-logs', f, { preserveState: true, replace: true });
  }

  function handlePageChange(page: number) {
    router.get('/soc/activity-logs', { ...filters, page }, { preserveState: true, replace: true });
  }

  return (
    <SocLayout title="Activity Logs">
      <Head title="Activity Logs" />
      <FilterBar filters={filters ?? {}} onApply={handleFilter} fields={filterFields} />
      <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
              <tr>
                <th className="px-4 py-3 text-left">ID</th>
                <th className="px-4 py-3 text-left">Event</th>
                <th className="px-4 py-3 text-left">Description</th>
                <th className="px-4 py-3 text-left">Subject</th>
                <th className="px-4 py-3 text-left">Causer</th>
                <th className="px-4 py-3 text-left">Tenant</th>
                <th className="px-4 py-3 text-left">Time</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {(logs?.data ?? []).map((log) => (
                <tr key={log.id} className="hover:bg-gray-50">
                  <td className="px-4 py-2.5 text-gray-400 text-xs">{log.id}</td>
                  <td className="px-4 py-2.5 font-mono text-xs text-gray-700">{log.event ?? log.log_name}</td>
                  <td className="px-4 py-2.5 text-gray-600 max-w-xs truncate">{log.description}</td>
                  <td className="px-4 py-2.5 text-xs text-gray-500">{log.subject_type ? `${log.subject_type}#${log.subject_id}` : '—'}</td>
                  <td className="px-4 py-2.5 text-gray-500">{log.causer_id ?? '—'}</td>
                  <td className="px-4 py-2.5 text-gray-500">{log.tenant_id ?? '—'}</td>
                  <td className="px-4 py-2.5 text-gray-400 whitespace-nowrap">{formatDate(log.created_at)}</td>
                </tr>
              ))}
              {(logs?.data ?? []).length === 0 && (
                <tr><td colSpan={7} className="px-6 py-8 text-center text-gray-400">No logs found</td></tr>
              )}
            </tbody>
          </table>
        </div>
        <div className="px-6 py-4">
          <Pagination currentPage={logs?.current_page ?? 1} lastPage={logs?.last_page ?? 1} onPageChange={handlePageChange} />
        </div>
      </div>
    </SocLayout>
  );
}
