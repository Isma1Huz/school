import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import type { PageProps } from '@/types';

export function usePermissions() {
    const { auth } = usePage<PageProps>().props;
    const permissions: string[] = auth?.user?.permissions ?? [];
    const roles: string[] = auth?.user?.roles ?? [];

    return useMemo(
        () => ({
            can: (permission: string) => permissions.includes(permission),
            canAny: (list: string[]) => list.some((p) => permissions.includes(p)),
            canAll: (list: string[]) => list.every((p) => permissions.includes(p)),
            hasRole: (role: string) => roles.includes(role),
            isSuperAdmin: () => roles.includes('super_admin'),
            isTenantAdmin: () => roles.includes('tenant_admin'),
            permissions,
            roles,
        }),
        [permissions, roles],
    );
}
