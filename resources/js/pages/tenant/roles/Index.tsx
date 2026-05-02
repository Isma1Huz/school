import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import TenantLayout from '@/layouts/TenantLayout';
import { DataTable } from '@/components/shared/DataTable';
import { ConfirmationDialog } from '@/components/shared/ConfirmationDialog';
import { Button } from '@/components/ui/button';
import { Plus, Edit, Trash2 } from 'lucide-react';
import type { PageProps } from '@/types';
import type { ColumnDef } from '@tanstack/react-table';

interface Role { id: number; name: string; guard_name: string; permissions_count?: number; }
interface RolesIndexProps extends PageProps { roles: Role[]; }

export default function RolesIndex() {
    const { roles } = usePage<RolesIndexProps>().props;
    const [deleteTarget, setDeleteTarget] = useState<Role | null>(null);

    const columns: ColumnDef<Role>[] = [
        { accessorKey: 'name', header: 'Role Name' },
        { accessorKey: 'guard_name', header: 'Guard' },
        { accessorKey: 'permissions_count', header: 'Permissions' },
        {
            id: 'actions',
            cell: ({ row }) => (
                <div className="flex gap-2">
                    <Link href={`/roles/${row.original.id}/edit`}>
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
        <TenantLayout title="Roles" breadcrumbs={[{ label: 'Roles' }]} actions={
            <Link href="/roles/create"><Button><Plus className="mr-2 h-4 w-4" />New Role</Button></Link>
        }>
            <Head title="Roles" />
            <DataTable columns={columns} data={roles ?? []} searchable />
            <ConfirmationDialog
                open={!!deleteTarget}
                onOpenChange={(open) => !open && setDeleteTarget(null)}
                title={`Delete role "${deleteTarget?.name}"?`}
                description="Users assigned this role will lose its permissions."
                confirmLabel="Delete"
                destructive
                onConfirm={() => {
                    if (deleteTarget) router.delete(`/roles/${deleteTarget.id}`, { onFinish: () => setDeleteTarget(null) });
                }}
            />
        </TenantLayout>
    );
}
