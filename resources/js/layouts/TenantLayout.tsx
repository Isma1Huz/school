import React from 'react';
import AppLayout from './AppLayout';
import type { BreadcrumbItem } from '@/components/layout/Breadcrumbs';
import { Breadcrumbs } from '@/components/layout/Breadcrumbs';

interface TenantLayoutProps {
    children: React.ReactNode;
    title?: string;
    breadcrumbs?: BreadcrumbItem[];
    actions?: React.ReactNode;
}

export default function TenantLayout({ children, title, breadcrumbs, actions }: TenantLayoutProps) {
    return (
        <AppLayout>
            <div className="space-y-4">
                {/* Page header */}
                {(title || breadcrumbs || actions) && (
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div className="space-y-1">
                            {breadcrumbs && <Breadcrumbs items={breadcrumbs} />}
                            {title && (
                                <h1 className="text-xl font-semibold text-gray-900">{title}</h1>
                            )}
                        </div>
                        {actions && <div className="flex gap-2">{actions}</div>}
                    </div>
                )}

                {children}
            </div>
        </AppLayout>
    );
}
