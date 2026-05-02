import React from 'react';
import { Head } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';
import { useForm } from '@inertiajs/react';

export default function CreatePayment() {
    const { data, setData, post, processing, errors } = useForm({ amount: '', reference: '' });

    return (
        <TenantLayout
            title="Record Payment"
            breadcrumbs={[{ label: 'Finance', href: '/finance' }, { label: 'Payments', href: '/finance/payments' }, { label: 'New' }]}
        >
            <Head title="Record Payment" />
            <Card className="max-w-md">
                <CardContent className="pt-6">
                    <form onSubmit={(e) => { e.preventDefault(); post('/finance/payments'); }} className="space-y-4">
                        <TextInput label="Amount" type="number" step="0.01" value={data.amount} onChange={(e) => setData('amount', e.target.value)} error={errors.amount} required />
                        <TextInput label="Reference" value={data.reference} onChange={(e) => setData('reference', e.target.value)} error={errors.reference} />
                        <Button type="submit" disabled={processing}>{processing ? 'Saving…' : 'Record Payment'}</Button>
                    </form>
                </CardContent>
            </Card>
        </TenantLayout>
    );
}
