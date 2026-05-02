import { useState } from 'react';
import api from '@/lib/api';

export function useInventory() {
    const [loading, setLoading] = useState(false);
    const [products, setProducts] = useState<unknown[]>([]);

    async function loadProducts() {
        setLoading(true);
        try {
            const { data } = await api.get('/inventory/products');
            setProducts(data);
        } finally {
            setLoading(false);
        }
    }

    async function adjustStock(productId: number, quantity: number, reason: string) {
        const { data } = await api.post(`/inventory/products/${productId}/adjust-stock`, { quantity, reason });
        return data;
    }

    return { products, loading, loadProducts, adjustStock };
}
