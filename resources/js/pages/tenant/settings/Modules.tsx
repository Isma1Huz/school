import React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent } from '@/components/ui/card';
import type { Module, PageProps } from '@/types';

interface ModuleSettingsProps extends PageProps {
    modules: Module[];
    enabledModuleIds: number[];
}

export default function ModuleSettings() {
    const { modules, enabledModuleIds } = usePage<ModuleSettingsProps>().props;

    function toggle(moduleId: number, enabled: boolean) {
        router.patch('/settings/modules/toggle', { module_id: moduleId, is_enabled: !enabled });
    }

    return (
        <TenantLayout title="Module Settings" breadcrumbs={[{ label: 'Settings', href: '/settings' }, { label: 'Modules' }]}>
            <Head title="Module Settings" />
            <div className="space-y-3 max-w-2xl">
                {(modules ?? []).map((m) => {
                    const enabled = enabledModuleIds?.includes(m.id) ?? false;
                    return (
                        <Card key={m.id}>
                            <CardContent className="flex items-center justify-between py-4">
                                <div>
                                    <p className="font-medium">{m.name}</p>
                                    <p className="text-xs text-gray-500">{m.description}</p>
                                    {m.dependencies.length > 0 && (
                                        <p className="text-xs text-gray-400 mt-0.5">
                                            Requires: {m.dependencies.join(', ')}
                                        </p>
                                    )}
                                </div>
                                <button
                                    onClick={() => !m.is_core && toggle(m.id, enabled)}
                                    disabled={m.is_core}
                                    title={m.is_core ? 'Core module — always enabled' : undefined}
                                    className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${enabled ? 'bg-green-500' : 'bg-gray-300'} disabled:opacity-50`}
                                >
                                    <span className={`inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform ${enabled ? 'translate-x-5' : 'translate-x-1'}`} />
                                </button>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>
        </TenantLayout>
    );
}
