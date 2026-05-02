import React from 'react';
import { Head } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TextInput } from '@/components/forms/TextInput';
import { useForm } from '@inertiajs/react';

export default function CreateClass() {
    const { data, setData, post, processing, errors } = useForm({ name: '', grade: '' });

    return (
        <TenantLayout
            title="New Class"
            breadcrumbs={[{ label: 'Academics', href: '/academics' }, { label: 'Classes', href: '/academics/classes' }, { label: 'New' }]}
        >
            <Head title="New Class" />
            <Card className="max-w-md">
                <CardContent className="pt-6">
                    <form onSubmit={(e) => { e.preventDefault(); post('/academics/classes'); }} className="space-y-4">
                        <TextInput label="Class Name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} required />
                        <TextInput label="Grade" value={data.grade} onChange={(e) => setData('grade', e.target.value)} error={errors.grade} required />
                        <Button type="submit" disabled={processing}>{processing ? 'Creating…' : 'Create Class'}</Button>
                    </form>
                </CardContent>
            </Card>
        </TenantLayout>
    );
}
