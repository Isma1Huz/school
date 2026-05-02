export interface Tenant {
    id: number;
    name: string;
    slug: string;
    type: TenantType;
    email: string | null;
    phone: string | null;
    address: string | null;
    logo: string | null;
    favicon: string | null;
    primary_color: string;
    secondary_color: string;
    settings: Record<string, unknown>;
    subscription_plan_id: number | null;
    subscription_status: SubscriptionStatus;
    subscription_start_date: string | null;
    subscription_end_date: string | null;
    billing_cycle: 'monthly' | 'yearly' | null;
    max_users: number;
    max_storage_mb: number;
    addon_modules: string[];
    status: TenantStatus;
    created_at: string;
    updated_at: string;
}

export type TenantType = 'school' | 'business' | 'ngo' | 'church' | string;

export type TenantStatus = 'active' | 'inactive' | 'pending' | 'suspended';

export type SubscriptionStatus = 'trial' | 'active' | 'cancelled' | 'expired' | 'past_due';

export interface TenantSharedProps {
    id: number;
    name: string;
    slug: string;
    type: TenantType;
    primary_color: string;
    secondary_color: string;
    logo: string | null;
    favicon: string | null;
    subscription_status: SubscriptionStatus;
    status: TenantStatus;
    max_users: number;
    max_storage_mb: number;
}
