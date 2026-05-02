import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { StatCard } from '@/components/shared/StatCard';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { Building2, Users, Package, CreditCard, CheckCircle } from 'lucide-react';
import { formatDate } from '@/lib/formatters';
import type { PageProps } from '@/types';

interface RecentTenant {
  id: number;
  name: string;
  status: string;
  created_at: string;
}

interface DashboardStats {
  total_tenants: number;
  active_tenants: number;
  total_users: number;
  total_modules: number;
  active_plans: number;
  recent_tenants: RecentTenant[];
}

interface SuperAdminDashboardProps extends PageProps {
  stats: DashboardStats;
}

export default function SuperAdminDashboard() {
  const { stats } = usePage<SuperAdminDashboardProps>().props;
  const dashboardStats = stats ?? {} as DashboardStats;

  const statCards = [
    { label: 'Total Tenants',  value: dashboardStats.total_tenants  ?? 0, icon: Building2,    color: 'text-blue-600',   href: '/super-admin/tenants' },
    { label: 'Active Tenants', value: dashboardStats.active_tenants ?? 0, icon: CheckCircle,  color: 'text-green-600',  href: '/super-admin/tenants' },
    { label: 'Total Users',    value: dashboardStats.total_users    ?? 0, icon: Users,         color: 'text-indigo-600', href: '/super-admin/users' },
    { label: 'Total Modules',  value: dashboardStats.total_modules  ?? 0, icon: Package,       color: 'text-purple-600', href: '/super-admin/modules' },
    { label: 'Active Plans',   value: dashboardStats.active_plans   ?? 0, icon: CreditCard,    color: 'text-yellow-600', href: '/super-admin/plans' },
  ];

  return (
    <SuperAdminLayout>
      <Head title="Super Admin Dashboard" />
      <div className="space-y-6">
        <div>
          <h2 className="text-2xl font-bold text-gray-900">Dashboard</h2>
          <p className="mt-1 text-sm text-gray-500">Overview of your multitenant system</p>
        </div>

        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
          {statCards.map((card) => (
            <StatCard key={card.label} {...card} />
          ))}
        </div>

        <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
          <div className="border-b border-gray-100 px-6 py-4">
            <h3 className="text-base font-semibold text-gray-900">Recent Tenants</h3>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
                <tr>
                  <th className="px-6 py-3 text-left">Name</th>
                  <th className="px-6 py-3 text-left">Status</th>
                  <th className="px-6 py-3 text-left">Created</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {(dashboardStats.recent_tenants ?? []).map((t) => (
                  <tr key={t.id} className="hover:bg-gray-50">
                    <td className="px-6 py-3 font-medium text-gray-900">{t.name}</td>
                    <td className="px-6 py-3"><StatusBadge status={t.status} /></td>
                    <td className="px-6 py-3 text-gray-500">{formatDate(t.created_at)}</td>
                  </tr>
                ))}
                {(dashboardStats.recent_tenants ?? []).length === 0 && (
                  <tr><td colSpan={3} className="px-6 py-8 text-center text-gray-400">No tenants yet</td></tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </SuperAdminLayout>
  );
}
