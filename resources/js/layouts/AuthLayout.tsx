import React from 'react';
import { useTenant } from '@/hooks/useTenant';

interface AuthLayoutProps {
    children: React.ReactNode;
    title?: string;
    subtitle?: string;
}

export default function AuthLayout({ children, title, subtitle }: AuthLayoutProps) {
    const tenant = useTenant();

    return (
        <div className="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12">
            <div className="w-full max-w-md space-y-8">
                {/* Logo / Branding */}
                <div className="text-center">
                    {tenant?.logo ? (
                        <img
                            src={tenant.logo}
                            alt={tenant.name}
                            className="mx-auto h-12 w-auto"
                        />
                    ) : (
                        <div
                            className="mx-auto flex h-12 w-12 items-center justify-center rounded-full text-xl font-bold text-white"
                            style={{ backgroundColor: tenant?.primary_color ?? '#800020' }}
                        >
                            {tenant?.name?.charAt(0) ?? 'S'}
                        </div>
                    )}
                    {title && (
                        <h2 className="mt-4 text-2xl font-bold tracking-tight text-gray-900">
                            {title}
                        </h2>
                    )}
                    {subtitle && (
                        <p className="mt-1 text-sm text-gray-600">{subtitle}</p>
                    )}
                </div>

                {/* Card */}
                <div className="rounded-lg border bg-white p-8 shadow-sm">
                    {children}
                </div>
            </div>
        </div>
    );
}
