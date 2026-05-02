import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import type { PageProps } from '@/types';

export function useModuleAccess() {
    const { enabledModules } = usePage<PageProps>().props;

    return useMemo(
        () => ({
            isEnabled: (moduleKey: string) =>
                Array.isArray(enabledModules) && enabledModules.includes(moduleKey),
            enabledModules: enabledModules ?? [],
        }),
        [enabledModules],
    );
}
