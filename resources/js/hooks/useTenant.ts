import { usePage } from '@inertiajs/react';
import type { PageProps, TenantSharedProps } from '@/types';

export function useTenant(): TenantSharedProps | null {
    const { tenant } = usePage<PageProps>().props;
    return tenant ?? null;
}

export function useTenantRequired(): TenantSharedProps {
    const tenant = useTenant();
    if (!tenant) throw new Error('No tenant in context');
    return tenant;
}
