import React, { useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { PermissionTree } from '@/components/shared/PermissionTree';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';
import type { PageProps } from '@/types';

interface PermissionsSettingsProps extends PageProps {
    permissionNodes: Array<{ key: string; label: string; children?: Array<{ key: string; label: string }> }>;
    selectedPermissions: string[];
}

export default function PermissionsSettings() {
    const { permissionNodes, selectedPermissions } = usePage<PermissionsSettingsProps>().props;
    const [selected, setSelected] = useState<string[]>(selectedPermissions ?? []);

    function handleSave() {
        router.put('/settings/permissions', { permissions: selected });
    }

    return (
        <TenantLayout title="Permissions" breadcrumbs={[{ label: 'Settings', href: '/settings' }, { label: 'Permissions' }]}>
            <Head title="Permissions" />
            <div className="max-w-2xl space-y-4">
                <PermissionTree nodes={permissionNodes ?? []} selected={selected} onChange={setSelected} />
                <Button onClick={handleSave}>Save Permissions</Button>
            </div>
        </TenantLayout>
    );
}
