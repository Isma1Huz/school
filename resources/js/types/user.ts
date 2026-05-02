export interface User {
    id: number;
    tenant_id: number | null;
    name: string;
    email: string;
    email_verified_at: string | null;
    phone: string | null;
    avatar: string | null;
    user_type: UserType;
    preferences: Record<string, unknown>;
    settings: Record<string, unknown>;
    is_active: boolean;
    last_login_at: string | null;
    created_at: string;
    updated_at: string;
}

export type UserType = 'super_admin' | 'tenant_admin' | 'staff' | 'member' | string;

export interface UserPermissions {
    permissions: string[];
    roles: string[];
}

export interface AuthUser extends User, UserPermissions {}
