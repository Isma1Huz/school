import React from 'react';
import { Head } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTenant } from '@/hooks/useTenant';
import { useModuleAccess } from '@/hooks/useModuleAccess';

export default function TenantDashboard() {
    const tenant = useTenant();
    const { enabledModules } = useModuleAccess();

    return (
        <TenantLayout title="Dashboard">
            <Head title="Dashboard" />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card>
                    <CardHeader><CardTitle className="text-sm">Tenant</CardTitle></CardHeader>
                    <CardContent>
                        <p className="text-xl font-bold">{tenant?.name}</p>
                        <p className="text-xs text-muted-foreground capitalize">{tenant?.type}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle className="text-sm">Active Modules</CardTitle></CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold">{enabledModules.length}</p>
                    </CardContent>
                </Card>
            </div>
        </TenantLayout>
    );
}
