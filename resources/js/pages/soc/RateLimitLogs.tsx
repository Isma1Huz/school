import React, { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import SocLayout from '@/layouts/SocLayout';
import { FilterBar } from '@/components/shared/FilterBar';
import { Pagination } from '@/components/shared/Pagination';
import { ConfirmationDialog } from '@/components/shared/ConfirmationDialog';
import { Button } from '@/components/ui/button';
import { ShieldAlert } from 'lucide-react';
import { formatDate } from '@/lib/formatters';
import type { PageProps, PaginatedData, RateLimitLogEntry } from '@/types';

interface RateLimitLogsProps extends PageProps {
  logs: PaginatedData<RateLimitLogEntry>;
  tenants: Array<{ id: number; name: string }>;
  filters: Record<string, string>;
}

export default function RateLimitLogs() {
  const { logs, tenants, filters } = usePage<RateLimitLogsProps>().props;
  const [blockTarget, setBlockTarget] = useState<RateLimitLogEntry | null>(null);

  const tenantOptions = (tenants ?? []).map((t) => ({ value: String(t.id), label: t.name }));
  const filterFields = [
    { key: 'tenant_id', label: 'Tenant', type: 'select' as const, options: tenantOptions },
    { key: 'ip_address', label: 'IP Address', type: 'text' as const },
  ];

  function handleFilter(f: Record<string, string>) {
    router.get('/soc/rate-limit-logs', f, { preserveState: true, replace: true });
  }

  function handlePageChange(page: number) {
    router.get('/soc/rate-limit-logs', { ...filters, page }, { preserveState: true, replace: true });
  }

  function handleBlockIp() {
    if (!blockTarget) return;
    router.post('/soc/ip-blacklist', { ip_address: blockTarget.ip_address, reason: 'Rate limit exceeded' }, { onFinish: () => setBlockTarget(null) });
  }

  return (
    <SocLayout title="Rate Limit Logs">
      <Head title="Rate Limit Logs" />
      <FilterBar filters={filters ?? {}} onApply={handleFilter} fields={filterFields} />
      <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
              <tr>
                <th className="px-4 py-3 text-left">IP</th>
                <th className="px-4 py-3 text-left">Endpoint</th>
                <th className="px-4 py-3 text-left">Method</th>
                <th className="px-4 py-3 text-left">Type</th>
                <th className="px-4 py-3 text-left">Count/Limit</th>
                <th className="px-4 py-3 text-left">Blocked At</th>
                <th className="px-4 py-3 text-left">Tenant</th>
                <th className="px-4 py-3 text-left">User</th>
                <th className="px-4 py-3 text-left">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {(logs?.data ?? []).map((log) => (
                <tr key={log.id} className="hover:bg-gray-50">
                  <td className="px-4 py-2.5 font-mono text-xs text-gray-700">{log.ip_address}</td>
                  <td className="px-4 py-2.5 text-xs text-gray-600 max-w-[160px] truncate">{log.endpoint}</td>
                  <td className="px-4 py-2.5 text-xs font-medium text-gray-500">{log.method}</td>
                  <td className="px-4 py-2.5 text-xs text-gray-500">{log.rate_limit_type}</td>
                  <td className="px-4 py-2.5 text-xs text-gray-600">{log.requests_count}/{log.limit_value}</td>
                  <td className="px-4 py-2.5 text-xs text-gray-400 whitespace-nowrap">{formatDate(log.blocked_at)}</td>
                  <td className="px-4 py-2.5 text-xs text-gray-500">{log.tenant?.name ?? '—'}</td>
                  <td className="px-4 py-2.5 text-xs text-gray-500">{log.user?.name ?? '—'}</td>
                  <td className="px-4 py-2.5">
                    <Button size="sm" variant="outline" className="h-7 text-xs border-red-300 text-red-600 hover:bg-red-50" onClick={() => setBlockTarget(log)}>
                      <ShieldAlert className="mr-1 h-3 w-3" /> Block
                    </Button>
                  </td>
                </tr>
              ))}
              {(logs?.data ?? []).length === 0 && (
                <tr><td colSpan={9} className="px-6 py-8 text-center text-gray-400">No rate limit logs found</td></tr>
              )}
            </tbody>
          </table>
        </div>
        <div className="px-6 py-4">
          <Pagination currentPage={logs?.current_page ?? 1} lastPage={logs?.last_page ?? 1} onPageChange={handlePageChange} />
        </div>
      </div>

      <ConfirmationDialog
        open={!!blockTarget}
        onOpenChange={(open) => !open && setBlockTarget(null)}
        title={`Block IP ${blockTarget?.ip_address}?`}
        description="This will add the IP to the blacklist and block future requests."
        confirmLabel="Block IP"
        destructive
        onConfirm={handleBlockIp}
      />
    </SocLayout>
  );
}
