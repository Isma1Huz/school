import React from 'react';
import { Head, Link } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { DataTable } from '@/components/shared/DataTable';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';
import { formatDate } from '@/lib/formatters';
import type { ColumnDef } from '@tanstack/react-table';

interface Exam { id: number; title: string; class_name: string; date: string; }

const columns: ColumnDef<Exam>[] = [
    { accessorKey: 'title',      header: 'Exam Title' },
    { accessorKey: 'class_name', header: 'Class' },
    { accessorKey: 'date',       header: 'Date', cell: ({ getValue }) => formatDate(getValue() as string) },
];

export default function ExamsIndex() {
    return (
        <TenantLayout
            title="Exams"
            breadcrumbs={[{ label: 'Academics', href: '/academics' }, { label: 'Exams' }]}
            actions={<Link href="/academics/exams/create"><Button><Plus className="mr-2 h-4 w-4" />New Exam</Button></Link>}
        >
            <Head title="Exams" />
            <DataTable columns={columns} data={[]} searchable />
        </TenantLayout>
    );
}
