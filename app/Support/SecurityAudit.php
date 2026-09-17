<?php

namespace App\Support;

use App\Models\AccessLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SecurityAudit
{
    public static function record(Request $request, string $action, string $table, int $id, string $details): void
    {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'occurred_at' => now(),
            'action' => $action,
            'table_name' => $table,
            'record_id' => (string) $id,
            'details' => $details,
            'ip_address' => $request->ip(),
        ]);
    }

    public static function openAccess(Request $request): void
    {
        $log = AccessLog::create(['user_id' => $request->user()->id, 'logged_in_at' => now(), 'ip_address' => $request->ip()]);
        $request->session()->put('access_log_id', $log->id);
    }

    public static function closeAccess(Request $request, string $type): void
    {
        if ($request->user() && $request->session()->has('access_log_id')) {
            AccessLog::whereKey($request->session()->get('access_log_id'))
                ->where('user_id', $request->user()->id)->whereNull('logged_out_at')
                ->update(['logged_out_at' => now(), 'logout_type' => $type]);
        }
    }
}
