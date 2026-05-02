import React from 'react';
import { Head } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function InventoryDashboard() {
    return (
        <TenantLayout title="Inventory" breadcrumbs={[{ label: 'Inventory' }]}>
            <Head title="Inventory" />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {['Products', 'Low Stock', 'Purchase Orders'].map((label) => (
                    <Card key={label}>
                        <CardHeader><CardTitle className="text-sm">{label}</CardTitle></CardHeader>
                        <CardContent><p className="text-3xl font-bold">0</p></CardContent>
                    </Card>
                ))}
            </div>
        </TenantLayout>
    );
}
