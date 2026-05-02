import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';
import { SelectInput } from '@/components/forms/SelectInput';
import type { User, PageProps, SelectOption } from '@/types';

interface EditUserProps extends PageProps { user: User; }

const userTypes: SelectOption[] = [
    { value: 'tenant_admin', label: 'Admin' },
    { value: 'staff', label: 'Staff' },
    { value: 'member', label: 'Member' },
];

export default function EditUser() {
    const { user } = usePage<EditUserProps>().props;
    const { data, setData, put, processing, errors } = useForm({
        name: user.name, email: user.email, user_type: user.user_type,
    });

    return (
        <TenantLayout title="Edit User" breadcrumbs={[{ label: 'Users', href: '/users' }, { label: user.name }]}>
            <Head title="Edit User" />
            <Card className="max-w-md">
                <CardContent className="pt-6">
                    <form onSubmit={(e) => { e.preventDefault(); put(`/users/${user.id}`); }} className="space-y-4">
                        <TextInput label="Full Name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} required />
                        <TextInput label="Email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} error={errors.email} required />
                        <SelectInput label="Role" options={userTypes} value={data.user_type} onChange={(v) => setData('user_type', v)} />
                        <div className="flex gap-3">
                            <Button type="submit" disabled={processing}>{processing ? 'Saving…' : 'Save'}</Button>
                            <Button type="button" variant="outline" onClick={() => history.back()}>Cancel</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </TenantLayout>
    );
}
