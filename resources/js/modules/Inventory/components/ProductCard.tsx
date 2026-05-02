import React from 'react';
import { cn } from '@/lib/utils';

interface Product { id: number; name: string; sku: string; stock: number; price: number; image?: string; }

interface ProductCardProps {
    product: Product;
    lowStockThreshold?: number;
    onClick?: (product: Product) => void;
}

export function ProductCard({ product, lowStockThreshold = 10, onClick }: ProductCardProps) {
    const isLowStock = product.stock <= lowStockThreshold;

    return (
        <div
            className={cn('cursor-pointer rounded-md border bg-white p-4 shadow-sm hover:shadow-md transition-shadow', onClick && 'cursor-pointer')}
            onClick={() => onClick?.(product)}
        >
            <div className="flex items-start justify-between">
                <div>
                    <p className="font-medium text-gray-900">{product.name}</p>
                    <p className="text-xs text-gray-500">SKU: {product.sku}</p>
                </div>
                <p className="text-sm font-semibold text-primary">KES {product.price}</p>
            </div>
            <div className="mt-2 flex items-center gap-2">
                <span className={cn('text-xs font-medium', isLowStock ? 'text-red-600' : 'text-gray-600')}>
                    Stock: {product.stock}
                </span>
                {isLowStock && (
                    <span className="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">Low Stock</span>
                )}
            </div>
        </div>
    );
}
