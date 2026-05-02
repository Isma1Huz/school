import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';
import { SelectInput } from '@/components/forms/SelectInput';
import type { SelectOption } from '@/types';

const userTypes: SelectOption[] = [
    { value: 'tenant_admin', label: 'Admin' },
    { value: 'staff',        label: 'Staff' },
    { value: 'member',       label: 'Member' },
];

export default function CreateUser() {
    const { data, setData, post, processing, errors } = useForm({
        name: '', email: '', password: '', user_type: 'staff',
    });

    return (
        <TenantLayout title="New User" breadcrumbs={[{ label: 'Users', href: '/users' }, { label: 'New' }]}>
            <Head title="New User" />
            <Card className="max-w-md">
                <CardContent className="pt-6">
                    <form onSubmit={(e) => { e.preventDefault(); post('/users'); }} className="space-y-4">
                        <TextInput label="Full Name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} required />
                        <TextInput label="Email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} error={errors.email} required />
                        <TextInput label="Password" type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} error={errors.password} required />
                        <SelectInput label="Role" options={userTypes} value={data.user_type} onChange={(v) => setData('user_type', v)} error={errors.user_type} />
                        <div className="flex gap-3">
                            <Button type="submit" disabled={processing}>{processing ? 'Creating…' : 'Create User'}</Button>
                            <Button type="button" variant="outline" onClick={() => history.back()}>Cancel</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </TenantLayout>
    );
}
