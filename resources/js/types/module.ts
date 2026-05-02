export interface Module {
    id: number;
    name: string;
    key: string;
    icon: string | null;
    description: string | null;
    allowed_tenant_types: string[];
    dependencies: string[];
    default_permissions: string[];
    settings_schema: Record<string, ModuleSettingField>;
    sort_order: number;
    is_core: boolean;
    is_active: boolean;
    is_globally_disabled: boolean;
    pivot?: ModuleTenantPivot;
}

export interface ModuleTenantPivot {
    module_id: number;
    tenant_id: number;
    is_enabled: boolean;
    settings: Record<string, unknown>;
    permissions_override: Record<string, unknown>;
}

export interface ModuleSettingField {
    type: 'string' | 'integer' | 'boolean' | 'select';
    default: unknown;
    label?: string;
    options?: Array<{ value: string; label: string }>;
}

export interface ModuleRegistration {
    key: string;
    name: string;
    icon: string;
    description: string;
    routes: string[];
    permissions: string[];
}
