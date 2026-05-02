import React, { useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import SocLayout from '@/layouts/SocLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { Pagination } from '@/components/shared/Pagination';
import { ConfirmationDialog } from '@/components/shared/ConfirmationDialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, ShieldOff } from 'lucide-react';
import { formatDate } from '@/lib/formatters';
import type { PageProps, PaginatedData, IpBlacklistRecord } from '@/types';

interface IpManagementProps extends PageProps {
  records: PaginatedData<IpBlacklistRecord>;
}

function AddIpForm() {
  const { data, setData, post, processing, errors, reset } = useForm({
    ip_address: '', reason: '', expires_at: '',
  });

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    post('/soc/ip-blacklist', { onSuccess: () => reset() });
  }

  return (
    <Card className="mb-6">
      <CardHeader><CardTitle className="text-base">Block New IP</CardTitle></CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="flex flex-wrap items-end gap-3">
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-gray-600">IP Address *</label>
            <input type="text" value={data.ip_address} onChange={(e) => setData('ip_address', e.target.value)} placeholder="e.g. 192.168.1.1" className="h-9 rounded-md border border-gray-300 px-2 text-sm" required />
            {errors.ip_address && <p className="text-xs text-red-500">{errors.ip_address}</p>}
          </div>
          <div className="flex flex-col gap-1 flex-1 min-w-[200px]">
            <label className="text-xs font-medium text-gray-600">Reason</label>
            <input type="text" value={data.reason} onChange={(e) => setData('reason', e.target.value)} placeholder="Reason for blocking" className="h-9 rounded-md border border-gray-300 px-2 text-sm" />
          </div>
          <div className="flex flex-col gap-1">
            <label className="text-xs font-medium text-gray-600">Expires At</label>
            <input type="datetime-local" value={data.expires_at} onChange={(e) => setData('expires_at', e.target.value)} className="h-9 rounded-md border border-gray-300 px-2 text-sm" />
          </div>
          <Button type="submit" disabled={processing} className="h-9 bg-[#800020] hover:bg-[#6b001b]">
            <Plus className="mr-1.5 h-3.5 w-3.5" /> Block IP
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}

export default function IpManagement() {
  const { records } = usePage<IpManagementProps>().props;
  const [unblockTarget, setUnblockTarget] = useState<IpBlacklistRecord | null>(null);

  function handlePageChange(page: number) {
    router.get('/soc/ip-management', { page }, { preserveState: true, replace: true });
  }

  function handleUnblock() {
    if (!unblockTarget) return;
    router.delete(`/soc/ip-blacklist/${unblockTarget.id}`, { onFinish: () => setUnblockTarget(null) });
  }

  return (
    <SocLayout title="IP Management">
      <Head title="IP Management" />
      <PageHeader title="IP Management" description="Manage blocked IP addresses" breadcrumbs={[{ label: 'IP Management' }]} />
      <AddIpForm />
      <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
              <tr>
                <th className="px-6 py-3 text-left">IP Address</th>
                <th className="px-6 py-3 text-left">Reason</th>
                <th className="px-6 py-3 text-left">Blocked By</th>
                <th className="px-6 py-3 text-left">Expires At</th>
                <th className="px-6 py-3 text-left">Status</th>
                <th className="px-6 py-3 text-left">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {(records?.data ?? []).map((rec) => (
                <tr key={rec.id} className="hover:bg-gray-50">
                  <td className="px-6 py-3 font-mono text-xs text-gray-800">{rec.ip_address}</td>
                  <td className="px-6 py-3 text-gray-600">{rec.reason ?? '—'}</td>
                  <td className="px-6 py-3 text-gray-500">{rec.blocked_by?.name ?? '—'}</td>
                  <td className="px-6 py-3 text-gray-500">{rec.expires_at ? formatDate(rec.expires_at) : 'Never'}</td>
                  <td className="px-6 py-3"><StatusBadge status={rec.is_active ? 'active' : 'inactive'} /></td>
                  <td className="px-6 py-3">
                    {rec.is_active && (
                      <Button size="sm" variant="outline" className="h-7 text-xs" onClick={() => setUnblockTarget(rec)}>
                        <ShieldOff className="mr-1 h-3 w-3" /> Unblock
                      </Button>
                    )}
                  </td>
                </tr>
              ))}
              {(records?.data ?? []).length === 0 && (
                <tr><td colSpan={6} className="px-6 py-8 text-center text-gray-400">No blocked IPs</td></tr>
              )}
            </tbody>
          </table>
        </div>
        <div className="px-6 py-4">
          <Pagination currentPage={records?.current_page ?? 1} lastPage={records?.last_page ?? 1} onPageChange={handlePageChange} />
        </div>
      </div>

      <ConfirmationDialog
        open={!!unblockTarget}
        onOpenChange={(open) => !open && setUnblockTarget(null)}
        title={`Unblock ${unblockTarget?.ip_address}?`}
        description="This IP will be allowed to make requests again."
        confirmLabel="Unblock"
        onConfirm={handleUnblock}
      />
    </SocLayout>
  );
}
