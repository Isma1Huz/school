import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';
import { SelectInput } from '@/components/forms/SelectInput';
import type { User, PageProps, SelectOption } from '@/types';

interface Role {
    id: number;
    name: string;
}

interface EditUserProps extends PageProps {
    user: User;
    roles: Role[];
    userRoles: string[];
}

const userTypes: SelectOption[] = [
    { value: 'tenant_admin', label: 'Admin' },
    { value: 'staff', label: 'Staff' },
    { value: 'member', label: 'Member' },
];

export default function EditUser() {
    const { user, roles, userRoles } = usePage<EditUserProps>().props;
    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        user_type: user.user_type,
        roles: userRoles ?? [] as string[],
    });

    function toggleRole(roleName: string) {
        setData('roles', data.roles.includes(roleName)
            ? data.roles.filter((r) => r !== roleName)
            : [...data.roles, roleName],
        );
    }

    return (
        <TenantLayout title="Edit User" breadcrumbs={[{ label: 'Users', href: '/users' }, { label: user.name }]}>
            <Head title="Edit User" />
            <Card className="max-w-md">
                <CardContent className="pt-6">
                    <form onSubmit={(e) => { e.preventDefault(); put(`/users/${user.id}`); }} className="space-y-4">
                        <TextInput
                            label="Full Name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            error={errors.name}
                            required
                        />
                        <TextInput
                            label="Email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            error={errors.email}
                            required
                        />
                        <SelectInput
                            label="User Type"
                            options={userTypes}
                            value={data.user_type}
                            onChange={(v) => setData('user_type', v)}
                        />

                        {/* Tenant-scoped role assignment */}
                        {roles && roles.length > 0 && (
                            <div className="space-y-2">
                                <p className="text-sm font-medium text-gray-700">Assign Roles</p>
                                <div className="space-y-1 rounded-md border p-3">
                                    {roles.map((role) => (
                                        <label key={role.id} className="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                                            <input
                                                type="checkbox"
                                                checked={data.roles.includes(role.name)}
                                                onChange={() => toggleRole(role.name)}
                                                className="h-4 w-4 rounded border-gray-300 text-primary"
                                            />
                                            <span className="capitalize">{role.name.replace(/_/g, ' ')}</span>
                                        </label>
                                    ))}
                                </div>
                                {errors.roles && <p className="text-xs text-red-600">{errors.roles}</p>}
                            </div>
                        )}

                        <div className="flex gap-3">
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Saving…' : 'Save'}
                            </Button>
                            <Button type="button" variant="outline" onClick={() => history.back()}>
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </TenantLayout>
    );
}
