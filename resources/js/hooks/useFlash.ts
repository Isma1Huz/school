import { usePage } from '@inertiajs/react';
import type { PageProps, FlashMessages } from '@/types';

export function useFlash(): FlashMessages {
    const { flash } = usePage<PageProps>().props;
    return flash ?? {};
}
