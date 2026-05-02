import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import type { PageProps } from '@/types';

export function useSubscriptionLimits() {
    const { tenant } = usePage<PageProps>().props;

    return useMemo(() => {
        if (!tenant) {
            return {
                maxUsers: 0,
                maxStorageMb: 0,
                isTrialing: false,
                isActive: false,
            };
        }

        return {
            maxUsers: tenant.max_users,
            maxStorageMb: tenant.max_storage_mb,
            isTrialing: tenant.subscription_status === 'trial',
            isActive: tenant.status === 'active',
        };
    }, [tenant]);
}
