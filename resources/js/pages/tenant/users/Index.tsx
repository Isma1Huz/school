import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { DataTable } from '@/components/shared/DataTable';
import { ConfirmationDialog } from '@/components/shared/ConfirmationDialog';
import { Button } from '@/components/ui/button';
import { Plus, Edit, Trash2 } from 'lucide-react';
import { formatDate } from '@/lib/formatters';
import type { User, PageProps, PaginatedData } from '@/types';
import type { ColumnDef } from '@tanstack/react-table';

interface UsersIndexProps extends PageProps {
    users: PaginatedData<User>;
}

export default function UsersIndex() {
    const { users } = usePage<UsersIndexProps>().props;
    const [deleteTarget, setDeleteTarget] = useState<User | null>(null);

    const columns: ColumnDef<User>[] = [
        { accessorKey: 'name',      header: 'Name' },
        { accessorKey: 'email',     header: 'Email' },
        { accessorKey: 'user_type', header: 'Role' },
        {
            accessorKey: 'is_active',
            header: 'Active',
            cell: ({ getValue }) => (
                <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${getValue() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'}`}>
                    {getValue() ? 'Active' : 'Inactive'}
                </span>
            ),
        },
        { accessorKey: 'created_at', header: 'Joined', cell: ({ getValue }) => formatDate(getValue() as string) },
        {
            id: 'actions',
            cell: ({ row }) => (
                <div className="flex gap-2">
                    <Link href={`/users/${row.original.id}/edit`}>
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
        <TenantLayout title="Users" breadcrumbs={[{ label: 'Users' }]} actions={
            <Link href="/users/create"><Button><Plus className="mr-2 h-4 w-4" />New User</Button></Link>
        }>
            <Head title="Users" />
            <DataTable columns={columns} data={users?.data ?? []} searchable />
            <ConfirmationDialog
                open={!!deleteTarget}
                onOpenChange={(open) => !open && setDeleteTarget(null)}
                title={`Delete ${deleteTarget?.name}?`}
                description="The user will be soft-deleted."
                confirmLabel="Delete"
                destructive
                onConfirm={() => {
                    if (deleteTarget) router.delete(`/users/${deleteTarget.id}`, { onFinish: () => setDeleteTarget(null) });
                }}
            />
        </TenantLayout>
    );
}
