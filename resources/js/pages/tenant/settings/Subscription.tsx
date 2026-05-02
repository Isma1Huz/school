import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { UsageMeter } from '@/components/shared/UsageMeter';
import { formatDate, formatBytes } from '@/lib/formatters';
import type { Tenant, PageProps } from '@/types';

interface SubscriptionSettingsProps extends PageProps {
    tenant: Tenant;
    usedUsers: number;
    usedStorageMb: number;
}

export default function SubscriptionSettings() {
    const { tenant, usedUsers, usedStorageMb } = usePage<SubscriptionSettingsProps>().props;

    return (
        <TenantLayout title="Subscription" breadcrumbs={[{ label: 'Settings', href: '/settings' }, { label: 'Subscription' }]}>
            <Head title="Subscription" />
            <div className="max-w-xl space-y-4">
                <Card>
                    <CardHeader><CardTitle>Current Plan</CardTitle></CardHeader>
                    <CardContent className="space-y-2 text-sm">
                        <Row label="Status"       value={tenant?.subscription_status} />
                        <Row label="Start Date"   value={formatDate(tenant?.subscription_start_date)} />
                        <Row label="End Date"     value={formatDate(tenant?.subscription_end_date)} />
                        <Row label="Billing Cycle" value={tenant?.billing_cycle ?? '—'} />
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle>Usage</CardTitle></CardHeader>
                    <CardContent className="space-y-4">
                        <UsageMeter label="Users" used={usedUsers ?? 0} total={tenant?.max_users ?? 0} />
                        <UsageMeter
                            label="Storage"
                            used={usedStorageMb ?? 0}
                            total={tenant?.max_storage_mb ?? 0}
                            formatValue={(v) => formatBytes(v * 1024 * 1024)}
                        />
                    </CardContent>
                </Card>
            </div>
        </TenantLayout>
    );
}

function Row({ label, value }: { label: string; value?: string | null }) {
    return (
        <div className="flex gap-2">
            <span className="w-32 shrink-0 font-medium text-gray-500">{label}</span>
            <span>{value ?? '—'}</span>
        </div>
    );
}
