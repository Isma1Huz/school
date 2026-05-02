import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';

export default function CreateSubscriptionPlan() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        code: '',
        description: '',
        price_monthly: '',
        price_yearly: '',
        max_users: '',
        max_storage_mb: '',
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/super-admin/plans');
    }

    return (
        <SuperAdminLayout title="New Subscription Plan">
            <Head title="New Plan" />
            <Card className="max-w-xl">
                <CardContent className="pt-6">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <TextInput label="Plan Name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} required />
                        <TextInput label="Code" value={data.code} onChange={(e) => setData('code', e.target.value)} error={errors.code} required />
                        <TextInput label="Description" value={data.description} onChange={(e) => setData('description', e.target.value)} error={errors.description} />
                        <div className="grid grid-cols-2 gap-4">
                            <TextInput label="Monthly Price" type="number" step="0.01" value={data.price_monthly} onChange={(e) => setData('price_monthly', e.target.value)} error={errors.price_monthly} />
                            <TextInput label="Yearly Price" type="number" step="0.01" value={data.price_yearly} onChange={(e) => setData('price_yearly', e.target.value)} error={errors.price_yearly} />
                            <TextInput label="Max Users" type="number" value={data.max_users} onChange={(e) => setData('max_users', e.target.value)} error={errors.max_users} />
                            <TextInput label="Max Storage (MB)" type="number" value={data.max_storage_mb} onChange={(e) => setData('max_storage_mb', e.target.value)} error={errors.max_storage_mb} />
                        </div>
                        <div className="flex gap-3">
                            <Button type="submit" disabled={processing}>{processing ? 'Creating…' : 'Create Plan'}</Button>
                            <Button type="button" variant="outline" onClick={() => history.back()}>Cancel</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </SuperAdminLayout>
    );
}
