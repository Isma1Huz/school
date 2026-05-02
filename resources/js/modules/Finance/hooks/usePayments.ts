import { useState } from 'react';
import api from '@/lib/api';

export function usePayments() {
    const [loading, setLoading] = useState(false);
    const [payments, setPayments] = useState<unknown[]>([]);

    async function load() {
        setLoading(true);
        try {
            const { data } = await api.get('/finance/payments');
            setPayments(data);
        } finally {
            setLoading(false);
        }
    }

    async function recordPayment(payload: { amount: number; reference: string }) {
        const { data } = await api.post('/finance/payments', payload);
        return data;
    }

    return { payments, loading, load, recordPayment };
}
