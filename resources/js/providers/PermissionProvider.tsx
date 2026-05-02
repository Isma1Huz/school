import React, { createContext, useContext, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';

interface PermissionContextValue {
    permissions: string[];
    roles: string[];
    can: (permission: string) => boolean;
    hasRole: (role: string) => boolean;
}

const PermissionContext = createContext<PermissionContextValue>({
    permissions: [],
    roles: [],
    can: () => false,
    hasRole: () => false,
});

export function PermissionProvider({ children }: { children: React.ReactNode }) {
    const { auth } = usePage<PageProps>().props;

    const value = useMemo<PermissionContextValue>(() => {
        const permissions: string[] = auth?.user?.permissions ?? [];
        const roles: string[] = auth?.user?.roles ?? [];

        return {
            permissions,
            roles,
            can: (p) => permissions.includes(p),
            hasRole: (r) => roles.includes(r),
        };
    }, [auth]);

    return (
        <PermissionContext.Provider value={value}>
            {children}
        </PermissionContext.Provider>
    );
}

export function usePermissionContext(): PermissionContextValue {
    return useContext(PermissionContext);
}
