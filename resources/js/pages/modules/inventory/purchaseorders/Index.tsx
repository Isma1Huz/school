import React from 'react';
import { Head } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { DataTable } from '@/components/shared/DataTable';
import { formatDate } from '@/lib/formatters';
import type { ColumnDef } from '@tanstack/react-table';

interface PurchaseOrder { id: number; number: string; supplier: string; status: string; date: string; }

const columns: ColumnDef<PurchaseOrder>[] = [
    { accessorKey: 'number',   header: 'PO Number' },
    { accessorKey: 'supplier', header: 'Supplier' },
    { accessorKey: 'status',   header: 'Status' },
    { accessorKey: 'date',     header: 'Date', cell: ({ getValue }) => formatDate(getValue() as string) },
];

export default function PurchaseOrdersIndex() {
    return (
        <TenantLayout
            title="Purchase Orders"
            breadcrumbs={[{ label: 'Inventory', href: '/inventory' }, { label: 'Purchase Orders' }]}
        >
            <Head title="Purchase Orders" />
            <DataTable columns={columns} data={[]} searchable />
        </TenantLayout>
    );
}
