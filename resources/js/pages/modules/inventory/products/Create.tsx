import React from 'react';
import { Head } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';
import { useForm } from '@inertiajs/react';

export default function CreateProduct() {
    const { data, setData, post, processing, errors } = useForm({ name: '', sku: '', price: '', stock: '0' });

    return (
        <TenantLayout
            title="New Product"
            breadcrumbs={[{ label: 'Inventory', href: '/inventory' }, { label: 'Products', href: '/inventory/products' }, { label: 'New' }]}
        >
            <Head title="New Product" />
            <Card className="max-w-md">
                <CardContent className="pt-6">
                    <form onSubmit={(e) => { e.preventDefault(); post('/inventory/products'); }} className="space-y-4">
                        <TextInput label="Product Name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} required />
                        <TextInput label="SKU" value={data.sku} onChange={(e) => setData('sku', e.target.value)} error={errors.sku} />
                        <TextInput label="Price" type="number" step="0.01" value={data.price} onChange={(e) => setData('price', e.target.value)} error={errors.price} />
                        <TextInput label="Initial Stock" type="number" value={data.stock} onChange={(e) => setData('stock', e.target.value)} />
                        <Button type="submit" disabled={processing}>{processing ? 'Creating…' : 'Create Product'}</Button>
                    </form>
                </CardContent>
            </Card>
        </TenantLayout>
    );
}
