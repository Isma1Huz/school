import { useState } from 'react';

export function useSidebar(defaultOpen = true) {
    const [isOpen, setIsOpen] = useState(defaultOpen);
    const [isMobileOpen, setIsMobileOpen] = useState(false);

    return {
        isOpen,
        isMobileOpen,
        toggle: () => setIsOpen((v) => !v),
        toggleMobile: () => setIsMobileOpen((v) => !v),
        open: () => setIsOpen(true),
        close: () => setIsOpen(false),
        closeMobile: () => setIsMobileOpen(false),
    };
}
