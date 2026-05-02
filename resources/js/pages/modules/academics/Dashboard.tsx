import React from 'react';
import { Head } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

export default function AcademicsDashboard() {
    return (
        <TenantLayout title="Academics" breadcrumbs={[{ label: 'Academics' }]}>
            <Head title="Academics" />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {['Classes', 'Students', 'Exams', 'Timetable'].map((item) => (
                    <Card key={item}>
                        <CardHeader><CardTitle className="text-sm">{item}</CardTitle></CardHeader>
                        <CardContent><p className="text-3xl font-bold">0</p></CardContent>
                    </Card>
                ))}
            </div>
        </TenantLayout>
    );
}
