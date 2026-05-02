import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { PageHeader } from '@/components/shared/PageHeader';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { ConfirmationDialog } from '@/components/shared/ConfirmationDialog';
import { Button } from '@/components/ui/button';
import { Plus, Edit, Trash2 } from 'lucide-react';
import type { PageProps, SubscriptionPlan, PaginatedData } from '@/types';

interface PlansIndexProps extends PageProps {
  plans: PaginatedData<SubscriptionPlan> | SubscriptionPlan[];
}

export default function PlansIndex() {
  const { plans } = usePage<PlansIndexProps>().props;
  const [deleteTarget, setDeleteTarget] = useState<SubscriptionPlan | null>(null);

  const planList: SubscriptionPlan[] = Array.isArray(plans) ? plans : (plans?.data ?? []);

  return (
    <SuperAdminLayout>
      <Head title="Subscription Plans" />
      <PageHeader
        title="Subscription Plans"
        description="Manage subscription plans"
        breadcrumbs={[{ label: 'Plans' }]}
        actions={
          <Link href="/super-admin/plans/create">
            <Button className="bg-[#800020] hover:bg-[#6b001b]">
              <Plus className="mr-2 h-4 w-4" /> New Plan
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
                <th className="px-6 py-3 text-left">Code</th>
                <th className="px-6 py-3 text-left">Price / mo</th>
                <th className="px-6 py-3 text-left">Max Users</th>
                <th className="px-6 py-3 text-left">Status</th>
                <th className="px-6 py-3 text-left">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {planList.map((plan) => (
                <tr key={plan.id} className="hover:bg-gray-50">
                  <td className="px-6 py-3 font-medium text-gray-900">{plan.name}</td>
                  <td className="px-6 py-3 font-mono text-xs text-gray-500">{plan.code}</td>
                  <td className="px-6 py-3 text-gray-600">{plan.price_monthly != null ? `$${plan.price_monthly}` : '—'}</td>
                  <td className="px-6 py-3 text-gray-600">{plan.max_users}</td>
                  <td className="px-6 py-3"><StatusBadge status={plan.is_active ? 'active' : 'inactive'} /></td>
                  <td className="px-6 py-3">
                    <div className="flex items-center gap-1">
                      <Link href={`/super-admin/plans/${plan.id}/edit`}><Button variant="ghost" size="icon"><Edit className="h-4 w-4" /></Button></Link>
                      <Button variant="ghost" size="icon" onClick={() => setDeleteTarget(plan)}><Trash2 className="h-4 w-4 text-red-500" /></Button>
                    </div>
                  </td>
                </tr>
              ))}
              {planList.length === 0 && (
                <tr><td colSpan={6} className="px-6 py-8 text-center text-gray-400">No plans found</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <ConfirmationDialog
        open={!!deleteTarget}
        onOpenChange={(open) => !open && setDeleteTarget(null)}
        title={`Delete ${deleteTarget?.name}?`}
        description="This will permanently delete the plan."
        confirmLabel="Delete"
        destructive
        onConfirm={() => {
          if (deleteTarget) {
            router.delete(`/super-admin/plans/${deleteTarget.id}`, { onFinish: () => setDeleteTarget(null) });
          }
        }}
      />
    </SuperAdminLayout>
  );
}
