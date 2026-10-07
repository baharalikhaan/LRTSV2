<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Admin-only user activity log viewer: sign-ins (with IP and session duration)
 * and the actions users performed.
 */
class AdminActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->isAdmin()) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('id');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('description', 'like', "%{$q}%")
                  ->orWhere('ip', 'like', "%{$q}%")
                  ->orWhere('url', 'like', "%{$q}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        $today = now()->startOfDay();
        $stats = [
            'logins_today'       => ActivityLog::where('event', 'login')->where('created_at', '>=', $today)->count(),
            'active_users_today' => ActivityLog::where('created_at', '>=', $today)->distinct()->count('user_id'),
            'total'              => ActivityLog::count(),
        ];

        $users  = User::orderBy('name')->get(['id', 'name', 'email']);
        $events = ['login', 'logout', 'view', 'action'];

        return view('admin.activity', compact('logs', 'users', 'events', 'stats'));
    }

    public function clear()
    {
        ActivityLog::truncate();

        return redirect()->route('admin.activity')->with('success', 'Activity log cleared.');
    }
}
