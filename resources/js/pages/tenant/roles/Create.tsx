import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';
import { PermissionTree } from '@/components/shared/PermissionTree';
import type { PageProps } from '@/types';

interface CreateRoleProps extends PageProps {
    permissionNodes: Array<{ key: string; label: string; children?: Array<{ key: string; label: string }> }>;
}

export default function CreateRole() {
    const { permissionNodes } = usePage<CreateRoleProps>().props;
    const [selectedPermissions, setSelectedPermissions] = useState<string[]>([]);
    const { data, setData, post, processing, errors } = useForm({ name: '', permissions: [] as string[] });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        setData('permissions', selectedPermissions);
        post('/roles');
    }

    return (
        <TenantLayout title="New Role" breadcrumbs={[{ label: 'Roles', href: '/roles' }, { label: 'New' }]}>
            <Head title="New Role" />
            <Card className="max-w-2xl">
                <CardContent className="pt-6">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <TextInput label="Role Name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} required />
                        <div className="space-y-2">
                            <p className="text-sm font-medium">Permissions</p>
                            <PermissionTree nodes={permissionNodes ?? []} selected={selectedPermissions} onChange={setSelectedPermissions} />
                        </div>
                        <div className="flex gap-3">
                            <Button type="submit" disabled={processing}>{processing ? 'Creating…' : 'Create Role'}</Button>
                            <Button type="button" variant="outline" onClick={() => history.back()}>Cancel</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </TenantLayout>
    );
}
