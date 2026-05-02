<?php

namespace App\Http\Controllers\Soc;

use App\Http\Controllers\Controller;
use App\Models\IpBlacklist;
use App\Models\RateLimitLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SocController extends Controller
{
    public function dashboard(): Response
    {
        $today = now()->startOfDay();

        $stats = [
            'total_requests_today' => DB::table('activity_log')
                ->whereDate('created_at', today())
                ->count(),
            'failed_jobs_count'    => DB::table('failed_jobs')->count(),
            'active_tenants'       => Tenant::where('status', 'active')->count(),
            'blocked_ips'          => IpBlacklist::where('is_active', true)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->count(),
            'recent_activity'      => DB::table('activity_log')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn ($row) => [
                    'id'           => $row->id,
                    'log_name'     => $row->log_name,
                    'description'  => $row->description,
                    'causer_type'  => $row->causer_type,
                    'causer_id'    => $row->causer_id,
                    'event'        => $row->event ?? null,
                    'tenant_id'    => $row->tenant_id ?? null,
                    'created_at'   => $row->created_at,
                ]),
        ];

        return Inertia::render('Soc/Dashboard', compact('stats'));
    }

    public function activityLogs(Request $request): Response
    {
        $query = DB::table('activity_log')->latest();

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->filled('action')) {
            $escaped = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->action);
            $query->where('description', 'like', '%' . $escaped . '%');
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->through(fn ($row) => [
            'id'           => $row->id,
            'log_name'     => $row->log_name,
            'description'  => $row->description,
            'subject_type' => $row->subject_type,
            'subject_id'   => $row->subject_id,
            'causer_type'  => $row->causer_type,
            'causer_id'    => $row->causer_id,
            'event'        => $row->event ?? null,
            'tenant_id'    => $row->tenant_id ?? null,
            'created_at'   => $row->created_at,
        ]);

        $tenants = Tenant::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Soc/ActivityLogs', [
            'logs'    => $logs,
            'tenants' => $tenants,
            'filters' => $request->only(['tenant_id', 'action', 'causer_id', 'date_from', 'date_to']),
        ]);
    }

    public function rateLimitLogs(Request $request): Response
    {
        $logs = RateLimitLog::with('tenant:id,name', 'user:id,name,email')
            ->when($request->tenant_id, fn ($q) => $q->where('tenant_id', $request->tenant_id))
            ->when($request->ip_address, fn ($q) => $q->where('ip_address', $request->ip_address))
            ->orderByDesc('blocked_at')
            ->paginate(20)
            ->through(fn ($log) => [
                'id'              => $log->id,
                'ip_address'      => $log->ip_address,
                'endpoint'        => $log->endpoint,
                'method'          => $log->method,
                'rate_limit_type' => $log->rate_limit_type,
                'requests_count'  => $log->requests_count,
                'limit_value'     => $log->limit_value,
                'blocked_at'      => $log->blocked_at,
                'tenant'          => $log->tenant ? ['name' => $log->tenant->name] : null,
                'user'            => $log->user ? ['name' => $log->user->name, 'email' => $log->user->email] : null,
            ]);

        $tenants = Tenant::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Soc/RateLimitLogs', [
            'logs'    => $logs,
            'tenants' => $tenants,
            'filters' => $request->only(['tenant_id', 'ip_address']),
        ]);
    }

    public function ipManagement(): Response
    {
        $records = IpBlacklist::with('blocker:id,name')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn ($r) => [
                'id'         => $r->id,
                'ip_address' => $r->ip_address,
                'reason'     => $r->reason,
                'is_active'  => $r->is_active,
                'expires_at' => $r->expires_at,
                'created_at' => $r->created_at,
                'blocked_by' => $r->blocker ? ['name' => $r->blocker->name] : null,
            ]);

        return Inertia::render('Soc/IpManagement', compact('records'));
    }
}
