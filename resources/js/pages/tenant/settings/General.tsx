import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';
import type { Tenant, PageProps } from '@/types';

interface GeneralSettingsProps extends PageProps {
    tenant: Tenant;
}

export default function GeneralSettings() {
    const { tenant } = usePage<GeneralSettingsProps>().props;

    const { data, setData, put, processing, errors } = useForm({
        name: tenant?.name ?? '',
        email: tenant?.email ?? '',
        phone: tenant?.phone ?? '',
        address: tenant?.address ?? '',
        primary_color: tenant?.primary_color ?? '#800020',
        secondary_color: tenant?.secondary_color ?? '#FFD700',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put('/settings/general');
    }

    return (
        <TenantLayout title="General Settings" breadcrumbs={[{ label: 'Settings', href: '/settings' }, { label: 'General' }]}>
            <Head title="General Settings" />
            <Card className="max-w-xl">
                <CardContent className="pt-6">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <TextInput label="Organisation Name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} required />
                        <TextInput label="Email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} error={errors.email} />
                        <TextInput label="Phone" value={data.phone} onChange={(e) => setData('phone', e.target.value)} error={errors.phone} />
                        <TextInput label="Address" value={data.address} onChange={(e) => setData('address', e.target.value)} error={errors.address} />
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1">
                                <label className="text-sm font-medium">Primary Colour</label>
                                <input type="color" value={data.primary_color} onChange={(e) => setData('primary_color', e.target.value)} className="h-10 w-full rounded-md border" />
                            </div>
                            <div className="space-y-1">
                                <label className="text-sm font-medium">Secondary Colour</label>
                                <input type="color" value={data.secondary_color} onChange={(e) => setData('secondary_color', e.target.value)} className="h-10 w-full rounded-md border" />
                            </div>
                        </div>
                        <Button type="submit" disabled={processing}>{processing ? 'Saving…' : 'Save Changes'}</Button>
                    </form>
                </CardContent>
            </Card>
        </TenantLayout>
    );
}
