export type { Tenant, TenantType, TenantStatus, SubscriptionStatus, TenantSharedProps } from './tenant';
export type { Module, ModuleTenantPivot, ModuleSettingField, ModuleRegistration } from './module';
export type { User, UserType, UserPermissions, AuthUser } from './user';

// ─── Subscription Plan ───────────────────────────────────────────────────────

export interface SubscriptionPlan {
    id: number;
    name: string;
    code: string;
    description: string | null;
    applicable_tenant_types: string[];
    price_monthly: number | null;
    price_yearly: number | null;
    max_users: number;
    max_storage_mb: number;
    features: string[];
    is_active: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;
}

// ─── Inertia shared page props ──────────────────────────────────────────────

import type { TenantSharedProps } from './tenant';
import type { AuthUser } from './user';

export interface PageProps {
    [key: string]: unknown;
    auth: {
        user: AuthUser | null;
    };
    tenant: TenantSharedProps | null;
    flash: {
        success?: string;
        error?: string;
        info?: string;
        warning?: string;
    };
    enabledModules: string[];
    ziggy?: Record<string, unknown>;
}

// ─── Navigation types ────────────────────────────────────────────────────────

export interface NavigationItem {
    name: string;
    href?: string;
    icon?: string;
    permission?: string;
    module?: string;
    children?: NavigationItem[];
    badge?: string | number;
    external?: boolean;
}

// ─── Generic API / pagination types ──────────────────────────────────────────

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface SelectOption<T = string> {
    value: T;
    label: string;
    disabled?: boolean;
}

export interface FlashMessages {
    success?: string;
    error?: string;
    info?: string;
    warning?: string;
}

// ─── Activity / Security Log types ───────────────────────────────────────────

export interface ActivityLogEntry {
  id: number;
  log_name: string;
  description: string;
  subject_type: string | null;
  subject_id: number | null;
  causer_type: string | null;
  causer_id: number | null;
  event: string | null;
  tenant_id: number | null;
  created_at: string;
}

export interface RateLimitLogEntry {
  id: number;
  ip_address: string;
  endpoint: string;
  method: string;
  rate_limit_type: string;
  requests_count: number;
  limit_value: number;
  blocked_at: string;
  tenant: { name: string } | null;
  user: { name: string; email: string } | null;
}

export interface IpBlacklistRecord {
  id: number;
  ip_address: string;
  reason: string | null;
  is_active: boolean;
  expires_at: string | null;
  created_at: string;
  blocked_by: { name: string } | null;
}
