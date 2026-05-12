<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'participant']);

        if ($search = $request->query('search')) {
            $query->search($search);
        }

        if ($module = $request->query('module')) {
            $query->forModule($module);
        }

        if ($action = $request->query('action')) {
            $query->forAction($action);
        }

        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $modules = ActivityLog::distinct()->pluck('module')->sort()->values();
        $actions = ActivityLog::distinct()->pluck('action')->sort()->values();
        $users   = User::orderBy('name')->get(['id', 'name']);

        return view('administration.logs.index', compact('logs', 'modules', 'actions', 'users'));
    }

    public function clear()
    {
        $deleted = ActivityLog::where('created_at', '<', now()->subDays(90))->delete();

        return redirect()->route('activity-logs.index')
            ->with('success', "Se eliminaron {$deleted} registro(s) con más de 90 días de antigüedad.");
    }
}
