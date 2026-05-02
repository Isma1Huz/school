import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';
import type { SubscriptionPlan, PageProps } from '@/types';

interface EditPlanProps extends PageProps {
    plan: SubscriptionPlan;
}

export default function EditSubscriptionPlan() {
    const { plan } = usePage<EditPlanProps>().props;

    const { data, setData, put, processing, errors } = useForm({
        name: plan.name,
        code: plan.code,
        description: plan.description ?? '',
        price_monthly: String(plan.price_monthly ?? ''),
        price_yearly: String(plan.price_yearly ?? ''),
        max_users: String(plan.max_users),
        max_storage_mb: String(plan.max_storage_mb),
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        put(`/super-admin/plans/${plan.id}`);
    }

    return (
        <SuperAdminLayout title={`Edit Plan: ${plan.name}`}>
            <Head title={`Edit ${plan.name}`} />
            <Card className="max-w-xl">
                <CardContent className="pt-6">
                    <form onSubmit={handleSubmit} className="space-y-4">
                        <TextInput label="Plan Name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} required />
                        <TextInput label="Code" value={data.code} onChange={(e) => setData('code', e.target.value)} error={errors.code} required />
                        <TextInput label="Description" value={data.description} onChange={(e) => setData('description', e.target.value)} />
                        <div className="grid grid-cols-2 gap-4">
                            <TextInput label="Monthly Price" type="number" step="0.01" value={data.price_monthly} onChange={(e) => setData('price_monthly', e.target.value)} error={errors.price_monthly} />
                            <TextInput label="Yearly Price" type="number" step="0.01" value={data.price_yearly} onChange={(e) => setData('price_yearly', e.target.value)} error={errors.price_yearly} />
                            <TextInput label="Max Users" type="number" value={data.max_users} onChange={(e) => setData('max_users', e.target.value)} />
                            <TextInput label="Max Storage (MB)" type="number" value={data.max_storage_mb} onChange={(e) => setData('max_storage_mb', e.target.value)} />
                        </div>
                        <div className="flex gap-3">
                            <Button type="submit" disabled={processing}>{processing ? 'Saving…' : 'Save Changes'}</Button>
                            <Button type="button" variant="outline" onClick={() => history.back()}>Cancel</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </SuperAdminLayout>
    );
}
