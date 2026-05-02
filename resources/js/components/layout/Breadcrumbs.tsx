import React from 'react';
import { Link } from '@inertiajs/react';
import { ChevronRight, Home } from 'lucide-react';
import { cn } from '@/lib/utils';

export interface BreadcrumbItem {
    label: string;
    href?: string;
}

interface BreadcrumbsProps {
    items: BreadcrumbItem[];
    className?: string;
}

export function Breadcrumbs({ items, className }: BreadcrumbsProps) {
    return (
        <nav aria-label="Breadcrumb" className={cn('flex items-center text-sm', className)}>
            <ol className="flex flex-wrap items-center gap-1">
                <li>
                    <Link href="/dashboard" className="text-gray-400 hover:text-gray-600">
                        <Home className="h-4 w-4" />
                    </Link>
                </li>
                {items.map((item, index) => (
                    <React.Fragment key={index}>
                        <li className="text-gray-300">
                            <ChevronRight className="h-4 w-4" />
                        </li>
                        <li>
                            {item.href && index < items.length - 1 ? (
                                <Link
                                    href={item.href}
                                    className="text-gray-500 hover:text-gray-700"
                                >
                                    {item.label}
                                </Link>
                            ) : (
                                <span className="font-medium text-gray-900">{item.label}</span>
                            )}
                        </li>
                    </React.Fragment>
                ))}
            </ol>
        </nav>
    );
}
