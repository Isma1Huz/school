import React from 'react';
import { Head, Link } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { DataTable } from '@/components/shared/DataTable';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';
import type { ColumnDef } from '@tanstack/react-table';

interface AcademicClass { id: number; name: string; grade: string; students_count: number; }

const columns: ColumnDef<AcademicClass>[] = [
    { accessorKey: 'name',           header: 'Class Name' },
    { accessorKey: 'grade',          header: 'Grade' },
    { accessorKey: 'students_count', header: 'Students' },
];

export default function ClassesIndex() {
    return (
        <TenantLayout
            title="Classes"
            breadcrumbs={[{ label: 'Academics', href: '/academics' }, { label: 'Classes' }]}
            actions={<Link href="/academics/classes/create"><Button><Plus className="mr-2 h-4 w-4" />New Class</Button></Link>}
        >
            <Head title="Classes" />
            <DataTable columns={columns} data={[]} searchable />
        </TenantLayout>
    );
}
