import React from 'react';
import { router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface TenantModuleEntry {
  id: number;
  name: string;
  key: string;
  is_active: boolean;
}

interface TenantModulesProps {
  tenantId: number;
  modules: TenantModuleEntry[];
}

export function TenantModules({ tenantId, modules }: TenantModulesProps) {
  function handleToggle(moduleId: number) {
    router.patch(`/super-admin/tenants/${tenantId}/modules/${moduleId}/toggle`);
  }

  return (
    <Card>
      <CardHeader><CardTitle className="text-base">Enabled Modules</CardTitle></CardHeader>
      <CardContent>
        {modules.length === 0 && (
          <p className="text-sm text-gray-400">No modules assigned</p>
        )}
        <div className="space-y-2">
          {modules.map((mod) => (
            <div key={mod.id} className="flex items-center justify-between rounded-lg border border-gray-100 px-4 py-2">
              <div>
                <p className="text-sm font-medium text-gray-800">{mod.name}</p>
                <p className="text-xs text-gray-400">{mod.key}</p>
              </div>
              <button
                onClick={() => handleToggle(mod.id)}
                className={`relative inline-flex h-5 w-9 items-center rounded-full transition-colors ${mod.is_active ? 'bg-green-500' : 'bg-gray-300'}`}
              >
                <span className={`inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform ${mod.is_active ? 'translate-x-4' : 'translate-x-1'}`} />
              </button>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
