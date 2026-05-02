<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function index(): Response
    {
        $modules = Module::withTrashed()
            ->withCount('tenants')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($m) => [
                'id'                  => $m->id,
                'key'                 => $m->key,
                'name'                => $m->name,
                'description'         => $m->description,
                'is_core'             => $m->is_core,
                'is_active'           => $m->is_active,
                'is_globally_disabled'=> $m->is_globally_disabled,
                'sort_order'          => $m->sort_order,
                'tenants_count'       => $m->tenants_count,
            ]);

        return Inertia::render('SuperAdmin/Modules/Index', compact('modules'));
    }

    public function update(Request $request, Module $module): RedirectResponse
    {
        $data = $request->validate([
            'is_active'           => ['sometimes', 'boolean'],
            'is_globally_disabled'=> ['sometimes', 'boolean'],
        ]);

        $module->update($data);

        return redirect()->route('super-admin.modules.index')
            ->with('success', 'Module updated successfully.');
    }

    public function tenantOverrides(Module $module): Response
    {
        $overrides = $module->tenants()
            ->withPivot(['is_enabled', 'settings'])
            ->get()
            ->map(fn ($t) => [
                'id'         => $t->id,
                'name'       => $t->name,
                'slug'       => $t->slug,
                'is_enabled' => $t->pivot->is_enabled,
                'settings'   => $t->pivot->settings,
            ]);

        return Inertia::render('SuperAdmin/Modules/TenantOverrides', [
            'module'    => [
                'id'   => $module->id,
                'key'  => $module->key,
                'name' => $module->name,
            ],
            'overrides' => $overrides,
        ]);
    }
}
