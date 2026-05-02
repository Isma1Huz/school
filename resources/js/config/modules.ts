import type { ModuleRegistration } from '@/types';

/**
 * Front-end module registry.
 * Add an entry here whenever a new back-end module is created.
 */
export const moduleRegistry: ModuleRegistration[] = [
    {
        key: 'core',
        name: 'Core',
        icon: 'LayoutDashboard',
        description: 'Core platform features',
        routes: ['/dashboard'],
        permissions: ['core:access_dashboard', 'core:manage_settings', 'core:manage_users'],
    },
    {
        key: 'academics',
        name: 'Academics',
        icon: 'GraduationCap',
        description: 'Classes, students, exams and timetable',
        routes: ['/academics'],
        permissions: [
            'academics:view',
            'academics:view_classes',
            'academics:view_students',
            'academics:view_exams',
            'academics:enter_marks',
            'academics:view_timetable',
        ],
    },
    {
        key: 'finance',
        name: 'Finance',
        icon: 'DollarSign',
        description: 'Invoices, payments and financial reports',
        routes: ['/finance'],
        permissions: [
            'finance:view',
            'finance:view_invoices',
            'finance:view_payments',
            'finance:view_reports',
        ],
    },
    {
        key: 'inventory',
        name: 'Inventory',
        icon: 'Package',
        description: 'Products, stock and purchase orders',
        routes: ['/inventory'],
        permissions: [
            'inventory:view',
            'inventory:view_products',
            'inventory:view_stock',
            'inventory:view_purchase_orders',
        ],
    },
    {
        key: 'hr',
        name: 'HR',
        icon: 'Users',
        description: 'Staff records, leave and payroll',
        routes: ['/hr'],
        permissions: ['hr:view', 'hr:manage_staff', 'hr:manage_leave'],
    },
    {
        key: 'communication',
        name: 'Communication',
        icon: 'MessageSquare',
        description: 'Announcements, messages and notifications',
        routes: ['/communication'],
        permissions: ['communication:view', 'communication:send'],
    },
];

/**
 * Lookup a module registration by key.
 */
export function getModuleByKey(key: string): ModuleRegistration | undefined {
    return moduleRegistry.find((m) => m.key === key);
}
