import React from 'react';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { StatusBadge } from '@/components/shared/StatusBadge';
import { Edit } from 'lucide-react';
import { formatDate } from '@/lib/formatters';
import type { Tenant } from '@/types';

interface TenantInfoCardProps {
  tenant: Tenant;
}

function Row({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <div className="flex gap-2 py-1.5 border-b border-gray-50 last:border-0">
      <span className="w-36 shrink-0 text-sm font-medium text-gray-500">{label}</span>
      <span className="text-sm text-gray-900">{children}</span>
    </div>
  );
}

export function TenantInfoCard({ tenant }: TenantInfoCardProps) {
  return (
    <div className="space-y-4">
      <div className="flex justify-end">
        <Link href={`/super-admin/tenants/${tenant.id}/edit`}>
          <Button variant="outline" size="sm"><Edit className="mr-2 h-4 w-4" /> Edit</Button>
        </Link>
      </div>
      <div className="grid gap-4 lg:grid-cols-2">
        <Card>
          <CardHeader><CardTitle className="text-base">General Information</CardTitle></CardHeader>
          <CardContent>
            <Row label="Name">{tenant.name}</Row>
            <Row label="Slug">{tenant.slug}</Row>
            <Row label="Type"><span className="capitalize">{tenant.type}</span></Row>
            <Row label="Email">{tenant.email ?? '—'}</Row>
            <Row label="Status"><StatusBadge status={tenant.status} /></Row>
            <Row label="Created">{formatDate(tenant.created_at)}</Row>
          </CardContent>
        </Card>
        <Card>
          <CardHeader><CardTitle className="text-base">Subscription</CardTitle></CardHeader>
          <CardContent>
            <Row label="Status"><StatusBadge status={tenant.subscription_status ?? 'inactive'} /></Row>
            <Row label="Start">{formatDate(tenant.subscription_start_date)}</Row>
            <Row label="End">{formatDate(tenant.subscription_end_date)}</Row>
            <Row label="Cycle">{tenant.billing_cycle ?? '—'}</Row>
            <Row label="Max Users">{String(tenant.max_users)}</Row>
            <Row label="Max Storage">{`${tenant.max_storage_mb} MB`}</Row>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
