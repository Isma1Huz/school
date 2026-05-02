import api from '@/lib/api';

export const academicsApi = {
    getClasses:   ()          => api.get('/academics/classes'),
    getStudents:  (classId: number) => api.get(`/academics/classes/${classId}/students`),
    getExams:     ()          => api.get('/academics/exams'),
    getTimetable: ()          => api.get('/academics/timetable'),
    submitMarks:  (examId: number, marks: Record<number, number>) =>
        api.post(`/academics/exams/${examId}/marks`, { marks }),
};
