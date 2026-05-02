import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import SocLayout from '@/layouts/SocLayout';
import { StatCard } from '@/components/shared/StatCard';
import { Activity, ShieldAlert, Building2, AlertCircle } from 'lucide-react';
import { formatDate } from '@/lib/formatters';
import type { PageProps, ActivityLogEntry } from '@/types';

interface SocStats {
  total_requests_today: number;
  failed_jobs_count: number;
  active_tenants: number;
  blocked_ips: number;
  recent_activity: ActivityLogEntry[];
}

interface SocDashboardProps extends PageProps {
  stats: SocStats;
}

export default function SocDashboard() {
  const { stats } = usePage<SocDashboardProps>().props;
  const socStats = stats ?? {} as SocStats;

  const statCards = [
    { label: 'Requests Today',  value: socStats.total_requests_today ?? 0, icon: Activity,    color: 'text-blue-600' },
    { label: 'Failed Jobs',     value: socStats.failed_jobs_count    ?? 0, icon: AlertCircle, color: 'text-red-600' },
    { label: 'Active Tenants',  value: socStats.active_tenants       ?? 0, icon: Building2,   color: 'text-green-600' },
    { label: 'Blocked IPs',     value: socStats.blocked_ips          ?? 0, icon: ShieldAlert, color: 'text-orange-600' },
  ];

  return (
    <SocLayout title="SOC Dashboard">
      <Head title="SOC Dashboard" />
      <div className="space-y-6">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {statCards.map((card) => <StatCard key={card.label} {...card} />)}
        </div>

        <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
          <div className="border-b border-gray-100 px-6 py-4">
            <h3 className="text-base font-semibold text-gray-900">Recent Activity</h3>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500">
                <tr>
                  <th className="px-4 py-3 text-left">Event</th>
                  <th className="px-4 py-3 text-left">Description</th>
                  <th className="px-4 py-3 text-left">Tenant</th>
                  <th className="px-4 py-3 text-left">Causer</th>
                  <th className="px-4 py-3 text-left">Time</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {(socStats.recent_activity ?? []).slice(0, 20).map((entry) => (
                  <tr key={entry.id} className="hover:bg-gray-50">
                    <td className="px-4 py-2.5 font-mono text-xs text-gray-700">{entry.event ?? entry.log_name}</td>
                    <td className="px-4 py-2.5 text-gray-600 max-w-xs truncate">{entry.description}</td>
                    <td className="px-4 py-2.5 text-gray-500">{entry.tenant_id ?? '—'}</td>
                    <td className="px-4 py-2.5 text-gray-500">{entry.causer_id ?? '—'}</td>
                    <td className="px-4 py-2.5 text-gray-400 whitespace-nowrap">{formatDate(entry.created_at)}</td>
                  </tr>
                ))}
                {(socStats.recent_activity ?? []).length === 0 && (
                  <tr><td colSpan={5} className="px-6 py-8 text-center text-gray-400">No recent activity</td></tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </SocLayout>
  );
}
