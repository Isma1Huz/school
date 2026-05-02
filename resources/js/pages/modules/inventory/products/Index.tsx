import React from 'react';
import { Head, Link } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { DataTable } from '@/components/shared/DataTable';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';
import type { ColumnDef } from '@tanstack/react-table';

interface Product { id: number; name: string; sku: string; stock: number; price: number; }

const columns: ColumnDef<Product>[] = [
    { accessorKey: 'name',  header: 'Product' },
    { accessorKey: 'sku',   header: 'SKU' },
    { accessorKey: 'stock', header: 'Stock' },
    { accessorKey: 'price', header: 'Price' },
];

export default function ProductsIndex() {
    return (
        <TenantLayout
            title="Products"
            breadcrumbs={[{ label: 'Inventory', href: '/inventory' }, { label: 'Products' }]}
            actions={<Link href="/inventory/products/create"><Button><Plus className="mr-2 h-4 w-4" />New Product</Button></Link>}
        >
            <Head title="Products" />
            <DataTable columns={columns} data={[]} searchable />
        </TenantLayout>
    );
}
