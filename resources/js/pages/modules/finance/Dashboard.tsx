import React from 'react';
import { Head } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function FinanceDashboard() {
    return (
        <TenantLayout title="Finance" breadcrumbs={[{ label: 'Finance' }]}>
            <Head title="Finance" />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {['Total Invoiced', 'Total Paid', 'Outstanding'].map((label) => (
                    <Card key={label}>
                        <CardHeader><CardTitle className="text-sm">{label}</CardTitle></CardHeader>
                        <CardContent><p className="text-3xl font-bold">KES 0</p></CardContent>
                    </Card>
                ))}
            </div>
        </TenantLayout>
    );
}
