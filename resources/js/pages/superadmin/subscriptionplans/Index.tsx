import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import SuperAdminLayout from '@/layouts/SuperAdminLayout';
import { DataTable } from '@/components/shared/DataTable';
import { Button } from '@/components/ui/button';
import { Plus, Edit, Trash2 } from 'lucide-react';
import { ConfirmationDialog } from '@/components/shared/ConfirmationDialog';
import { formatCurrency } from '@/lib/formatters';
import type { SubscriptionPlan, PageProps } from '@/types';
import type { ColumnDef } from '@tanstack/react-table';

interface PlansIndexProps extends PageProps {
    plans: SubscriptionPlan[];
}

export default function SubscriptionPlansIndex() {
    const { plans } = usePage<PlansIndexProps>().props;
    const [deleteTarget, setDeleteTarget] = useState<SubscriptionPlan | null>(null);

    const columns: ColumnDef<SubscriptionPlan>[] = [
        { accessorKey: 'name', header: 'Name' },
        { accessorKey: 'code', header: 'Code' },
        { accessorKey: 'price_monthly', header: 'Monthly', cell: ({ getValue }) => formatCurrency(getValue() as number) },
        { accessorKey: 'price_yearly',  header: 'Yearly',  cell: ({ getValue }) => formatCurrency(getValue() as number) },
        { accessorKey: 'max_users',     header: 'Max Users' },
        {
            accessorKey: 'is_active',
            header: 'Active',
            cell: ({ getValue }) => (
                <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${getValue() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'}`}>
                    {getValue() ? 'Active' : 'Inactive'}
                </span>
            ),
        },
        {
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => (
                <div className="flex gap-2">
                    <Link href={`/super-admin/plans/${row.original.id}/edit`}>
                        <Button variant="ghost" size="icon"><Edit className="h-4 w-4" /></Button>
                    </Link>
                    <Button variant="ghost" size="icon" onClick={() => setDeleteTarget(row.original)}>
                        <Trash2 className="h-4 w-4 text-red-500" />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <SuperAdminLayout title="Subscription Plans">
            <Head title="Subscription Plans" />
            <div className="flex justify-end mb-4">
                <Link href="/super-admin/plans/create">
                    <Button><Plus className="mr-2 h-4 w-4" /> New Plan</Button>
                </Link>
            </div>
            <DataTable columns={columns} data={plans ?? []} searchable />
            <ConfirmationDialog
                open={!!deleteTarget}
                onOpenChange={(open) => !open && setDeleteTarget(null)}
                title={`Delete plan "${deleteTarget?.name}"?`}
                description="This cannot be undone."
                confirmLabel="Delete"
                destructive
                onConfirm={() => {
                    if (deleteTarget) router.delete(`/super-admin/plans/${deleteTarget.id}`, { onFinish: () => setDeleteTarget(null) });
                }}
            />
        </SuperAdminLayout>
    );
}
