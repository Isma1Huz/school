import React from 'react';

interface MarksGridProps {
    students: Array<{ id: number; name: string }>;
    subjects: Array<{ id: number; name: string }>;
    marks: Record<string, Record<string, number | null>>;
    onMarkChange?: (studentId: number, subjectId: number, mark: number) => void;
}

export function MarksGrid({ students, subjects, marks, onMarkChange }: MarksGridProps) {
    return (
        <div className="overflow-auto rounded-md border">
            <table className="min-w-full text-sm">
                <thead className="bg-gray-50">
                    <tr>
                        <th className="px-3 py-2 text-left font-medium text-gray-600">Student</th>
                        {subjects.map((s) => (
                            <th key={s.id} className="px-3 py-2 text-left font-medium text-gray-600">{s.name}</th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {students.map((student) => (
                        <tr key={student.id} className="border-t">
                            <td className="px-3 py-2 font-medium text-gray-900">{student.name}</td>
                            {subjects.map((subject) => (
                                <td key={subject.id} className="px-3 py-2">
                                    <input
                                        type="number"
                                        min={0}
                                        max={100}
                                        value={marks[student.id]?.[subject.id] ?? ''}
                                        onChange={(e) => onMarkChange?.(student.id, subject.id, Number(e.target.value))}
                                        className="w-20 rounded border px-2 py-1 text-sm"
                                    />
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
