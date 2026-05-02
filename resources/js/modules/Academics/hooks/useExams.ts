import { useState } from 'react';
import api from '@/lib/api';

export function useExams() {
    const [loading, setLoading] = useState(false);
    const [exams, setExams] = useState<unknown[]>([]);

    async function load() {
        setLoading(true);
        try {
            const { data } = await api.get('/academics/exams');
            setExams(data);
        } finally {
            setLoading(false);
        }
    }

    async function submitMarks(examId: number, marks: Record<number, number>) {
        await api.post(`/academics/exams/${examId}/marks`, { marks });
    }

    return { exams, loading, load, submitMarks };
}
