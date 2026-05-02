import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';
import { PermissionTree } from '@/components/shared/PermissionTree';
import type { PageProps } from '@/types';

interface Role { id: number; name: string; permissions: Array<{ name: string }>; }
interface EditRoleProps extends PageProps {
    role: Role;
    permissionNodes: Array<{ key: string; label: string; children?: Array<{ key: string; label: string }> }>;
}

export default function EditRole() {
    const { role, permissionNodes } = usePage<EditRoleProps>().props;
    const [selectedPermissions, setSelectedPermissions] = useState<string[]>(
        role?.permissions?.map((p) => p.name) ?? [],
    );
    const { data, setData, put, processing, errors } = useForm({ name: role?.name ?? '', permissions: [] as string[] });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        setData('permissions', selectedPermissions);
        put(`/roles/${role.id}`);
    }

    return (
        <TenantLayout title="Edit Role" breadcrumbs={[{ label: 'Roles', href: '/roles' }, { label: role?.name }]}>
            <Head title="Edit Role" />
            <Card className="max-w-2xl">
                <CardContent className="pt-6">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <TextInput label="Role Name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} required />
                        <div className="space-y-2">
                            <p className="text-sm font-medium">Permissions</p>
                            <PermissionTree nodes={permissionNodes ?? []} selected={selectedPermissions} onChange={setSelectedPermissions} />
                        </div>
                        <div className="flex gap-3">
                            <Button type="submit" disabled={processing}>{processing ? 'Saving…' : 'Save Role'}</Button>
                            <Button type="button" variant="outline" onClick={() => history.back()}>Cancel</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </TenantLayout>
    );
}
