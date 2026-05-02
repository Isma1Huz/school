import React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Tenant, Module, PageProps } from '@/types';

interface TenantOverridesProps extends PageProps {
    tenant: Tenant;
    modules: Module[];
    enabledModuleIds: number[];
}

export default function TenantModuleOverrides() {
    const { tenant, modules, enabledModuleIds } = usePage<TenantOverridesProps>().props;

    function toggle(moduleId: number, enabled: boolean) {
        router.patch(`/super-admin/tenants/${tenant.id}/modules/${moduleId}`, { is_enabled: !enabled });
    }

    return (
        <SuperAdminLayout title={`Module Access – ${tenant?.name}`}>
            <Head title="Module Overrides" />
            <Card>
                <CardHeader><CardTitle>Enabled Modules</CardTitle></CardHeader>
                <CardContent>
                    <div className="space-y-3">
                        {(modules ?? []).map((m) => {
                            const enabled = enabledModuleIds?.includes(m.id) ?? false;
                            return (
                                <div key={m.id} className="flex items-center justify-between rounded-md border p-3">
                                    <div>
                                        <p className="font-medium text-gray-900">{m.name}</p>
                                        <p className="text-xs text-gray-500">{m.description}</p>
                                    </div>
                                    <button
                                        onClick={() => toggle(m.id, enabled)}
                                        className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${enabled ? 'bg-green-500' : 'bg-gray-300'}`}
                                    >
                                        <span className={`inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform ${enabled ? 'translate-x-5' : 'translate-x-1'}`} />
                                    </button>
                                </div>
                            );
                        })}
                    </div>
                </CardContent>
            </Card>
        </SuperAdminLayout>
    );
}
