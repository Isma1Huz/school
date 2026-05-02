import React from 'react';
import { ThemeProvider } from '@/providers/ThemeProvider';
import { TenantProvider } from '@/providers/TenantProvider';
import { PermissionProvider } from '@/providers/PermissionProvider';
import { LanguageProvider } from '@/providers/LanguageProvider';
import { Sidebar } from '@/components/layout/Sidebar';
import { Header } from '@/components/layout/Header';
import { Footer } from '@/components/layout/Footer';
import { FlashMessage } from '@/components/shared/FlashMessage';
import { useSidebar } from '@/hooks/useSidebar';

interface AppLayoutProps {
    children: React.ReactNode;
}

export default function AppLayout({ children }: AppLayoutProps) {
    const { isOpen, isMobileOpen, toggle, toggleMobile, closeMobile } = useSidebar(true);

    return (
        <LanguageProvider>
            <TenantProvider>
                <PermissionProvider>
                    <ThemeProvider>
                        <div className="min-h-screen bg-gray-50">
                            <Sidebar
                                isOpen={isOpen}
                                isMobileOpen={isMobileOpen}
                                onClose={closeMobile}
                            />

                            {/* Main content offset by sidebar width on desktop */}
                            <div className={`flex min-h-screen flex-col transition-all duration-200 ${isOpen ? 'lg:pl-64' : ''}`}>
                                <Header onMenuToggle={() => { toggle(); toggleMobile(); }} />

                                <main className="flex-1 p-6">
                                    <FlashMessage />
                                    {children}
                                </main>

                                <Footer />
                            </div>
                        </div>
                    </ThemeProvider>
                </PermissionProvider>
            </TenantProvider>
        </LanguageProvider>
    );
}
