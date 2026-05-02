import React, { createContext, useContext } from 'react';
import { usePage } from '@inertiajs/react';
import type { PageProps, TenantSharedProps } from '@/types';

const TenantContext = createContext<TenantSharedProps | null>(null);

export function TenantProvider({ children }: { children: React.ReactNode }) {
    const { tenant } = usePage<PageProps>().props;

    return (
        <TenantContext.Provider value={tenant ?? null}>
            {children}
        </TenantContext.Provider>
    );
}

export function useTenantContext(): TenantSharedProps | null {
    return useContext(TenantContext);
}
