import React from 'react';
import { Head } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { DataTable } from '@/components/shared/DataTable';
import { formatDate, formatCurrency } from '@/lib/formatters';
import type { ColumnDef } from '@tanstack/react-table';

interface Invoice { id: number; number: string; amount: number; status: string; due_date: string; }

const columns: ColumnDef<Invoice>[] = [
    { accessorKey: 'number',   header: 'Invoice #' },
    { accessorKey: 'amount',   header: 'Amount',   cell: ({ getValue }) => formatCurrency(getValue() as number) },
    { accessorKey: 'status',   header: 'Status' },
    { accessorKey: 'due_date', header: 'Due Date', cell: ({ getValue }) => formatDate(getValue() as string) },
];

export default function InvoicesIndex() {
    return (
        <TenantLayout title="Invoices" breadcrumbs={[{ label: 'Finance', href: '/finance' }, { label: 'Invoices' }]}>
            <Head title="Invoices" />
            <DataTable columns={columns} data={[]} searchable />
        </TenantLayout>
    );
}
