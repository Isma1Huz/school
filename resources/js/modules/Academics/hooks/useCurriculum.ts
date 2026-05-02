import { useState } from 'react';
import api from '@/lib/api';

export function useCurriculum() {
    const [loading, setLoading] = useState(false);
    const [curriculum, setCurriculum] = useState<unknown[]>([]);

    async function load() {
        setLoading(true);
        try {
            const { data } = await api.get('/academics/curriculum');
            setCurriculum(data);
        } finally {
            setLoading(false);
        }
    }

    return { curriculum, loading, load };
}
