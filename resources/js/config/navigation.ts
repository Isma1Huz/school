import type { NavigationItem } from '@/types';

const allItems: NavigationItem[] = [
    {
        name: 'Dashboard',
        href: '/dashboard',
        icon: 'LayoutDashboard',
        permission: 'core:access_dashboard',
        module: 'core',
    },
    {
        name: 'Academics',
        icon: 'GraduationCap',
        module: 'academics',
        children: [
            { name: 'Dashboard',  href: '/academics',          permission: 'academics:view',           module: 'academics' },
            { name: 'Classes',    href: '/academics/classes',   permission: 'academics:view_classes',   module: 'academics' },
            { name: 'Students',   href: '/academics/students',  permission: 'academics:view_students',  module: 'academics' },
            { name: 'Exams',      href: '/academics/exams',     permission: 'academics:view_exams',     module: 'academics' },
            { name: 'Marks',      href: '/academics/marks',     permission: 'academics:enter_marks',    module: 'academics' },
            { name: 'Timetable',  href: '/academics/timetable', permission: 'academics:view_timetable', module: 'academics' },
        ],
    },
    {
        name: 'Finance',
        icon: 'DollarSign',
        module: 'finance',
        children: [
            { name: 'Dashboard', href: '/finance',          permission: 'finance:view',          module: 'finance' },
            { name: 'Invoices',  href: '/finance/invoices', permission: 'finance:view_invoices', module: 'finance' },
            { name: 'Payments',  href: '/finance/payments', permission: 'finance:view_payments', module: 'finance' },
            { name: 'Reports',   href: '/finance/reports',  permission: 'finance:view_reports',  module: 'finance' },
        ],
    },
    {
        name: 'Inventory',
        icon: 'Package',
        module: 'inventory',
        children: [
            { name: 'Dashboard',       href: '/inventory',                  permission: 'inventory:view',                  module: 'inventory' },
            { name: 'Products',        href: '/inventory/products',         permission: 'inventory:view_products',         module: 'inventory' },
            { name: 'Purchase Orders', href: '/inventory/purchase-orders',  permission: 'inventory:view_purchase_orders',  module: 'inventory' },
            { name: 'Stock',           href: '/inventory/stock',            permission: 'inventory:view_stock',            module: 'inventory' },
        ],
    },
    {
        name: 'HR',
        icon: 'Users',
        module: 'hr',
        children: [
            { name: 'Dashboard', href: '/hr',         permission: 'hr:view',         module: 'hr' },
            { name: 'Staff',     href: '/hr/staff',   permission: 'hr:manage_staff', module: 'hr' },
            { name: 'Leave',     href: '/hr/leave',   permission: 'hr:manage_leave', module: 'hr' },
        ],
    },
    {
        name: 'Communication',
        icon: 'MessageSquare',
        module: 'communication',
        children: [
            { name: 'Announcements', href: '/communication/announcements', permission: 'communication:view', module: 'communication' },
            { name: 'Messages',      href: '/communication/messages',      permission: 'communication:send', module: 'communication' },
        ],
    },
    {
        name: 'Settings',
        href: '/settings',
        icon: 'Settings',
        permission: 'core:manage_settings',
        module: 'core',
    },
];

/**
 * Build the navigation tree filtered by enabled modules and user permissions.
 */
export function buildNavigation(
    enabledModules: string[],
    permissions: string[],
): NavigationItem[] {
    return filterNavigation(allItems, enabledModules, permissions);
}

function filterNavigation(
    items: NavigationItem[],
    enabledModules: string[],
    permissions: string[],
): NavigationItem[] {
    return items
        .filter((item) => !item.module || enabledModules.includes(item.module))
        .filter((item) => !item.permission || permissions.includes(item.permission))
        .map((item) => ({
            ...item,
            children: item.children
                ? filterNavigation(item.children, enabledModules, permissions)
                : undefined,
        }))
        .filter((item) => (item.children ? item.children.length > 0 : true));
}

export const superAdminNavItems = [
  { name: 'Dashboard',   href: '/super-admin',         icon: 'LayoutDashboard' },
  { name: 'Tenants',     href: '/super-admin/tenants',  icon: 'Building2' },
  { name: 'Modules',     href: '/super-admin/modules',  icon: 'Package' },
  { name: 'Plans',       href: '/super-admin/plans',    icon: 'CreditCard' },
  { name: 'Users',       href: '/super-admin/users',    icon: 'Users' },
  { name: 'SOC',         href: '/soc',                  icon: 'Shield' },
];

export const socNavItems = [
  { name: 'Dashboard',     href: '/soc',                  icon: 'LayoutDashboard' },
  { name: 'Activity Logs', href: '/soc/activity-logs',    icon: 'Activity' },
  { name: 'Rate Limits',   href: '/soc/rate-limit-logs',  icon: 'Gauge' },
  { name: 'IP Management', href: '/soc/ip-management',    icon: 'Shield' },
];
