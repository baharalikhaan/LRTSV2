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
        // Column sorting (server-side, so it applies across all pages).
        $sortable = [
            'when'     => 'activity_logs.created_at',
            'user'     => 'users.name',
            'event'    => 'activity_logs.event',
            'activity' => 'activity_logs.description',
            'ip'       => 'activity_logs.ip',
            'duration' => 'activity_logs.duration_seconds',
        ];
        $sort = (string) $request->input('sort', 'when');
        $dir  = strtolower((string) $request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortColumn = $sortable[$sort] ?? 'activity_logs.created_at';

        $query = ActivityLog::with('user')->select('activity_logs.*');
        if ($sort === 'user') {
            // Sorting by user name needs the users table.
            $query->leftJoin('users', 'users.id', '=', 'activity_logs.user_id');
        }
        $query->orderBy($sortColumn, $dir);

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
            // Distinct users who signed in today — matches the "Active Users
            // Today" list in the side panel exactly.
            'active_users_today' => ActivityLog::where('event', 'login')->where('created_at', '>=', $today)->distinct()->count('user_id'),
            'total'              => ActivityLog::count(),
        ];

        $users  = User::orderBy('name')->get(['id', 'name', 'email']);
        $events = ['login', 'logout', 'view', 'action'];

        // Users who signed in today (latest sign-in per user) — shown in the
        // side panel.
        $activeUsersToday = ActivityLog::with('user')
            ->where('event', 'login')
            ->where('created_at', '>=', $today)
            ->orderByDesc('created_at')
            ->get()
            ->unique('user_id')
            ->values();

        // Per-user metrics for today: number of activities performed and time
        // spent (completed session durations + the elapsed time of any session
        // that is still open, i.e. a sign-in with no later sign-out).
        $userIds = $activeUsersToday->pluck('user_id')->filter()->all();

        $activityCounts = ActivityLog::whereIn('user_id', $userIds)
            ->where('created_at', '>=', $today)
            ->whereIn('event', ['view', 'action'])
            ->groupBy('user_id')
            ->select('user_id', \DB::raw('COUNT(*) as c'))
            ->pluck('c', 'user_id');

        $logoutSeconds = ActivityLog::whereIn('user_id', $userIds)
            ->where('created_at', '>=', $today)
            ->where('event', 'logout')
            ->groupBy('user_id')
            ->select('user_id', \DB::raw('COALESCE(SUM(duration_seconds), 0) as s'))
            ->pluck('s', 'user_id');

        $latestLogin = ActivityLog::whereIn('user_id', $userIds)
            ->where('created_at', '>=', $today)
            ->where('event', 'login')
            ->groupBy('user_id')
            ->select('user_id', \DB::raw('MAX(created_at) as t'))
            ->pluck('t', 'user_id');

        $latestLogout = ActivityLog::whereIn('user_id', $userIds)
            ->where('created_at', '>=', $today)
            ->where('event', 'logout')
            ->groupBy('user_id')
            ->select('user_id', \DB::raw('MAX(created_at) as t'))
            ->pluck('t', 'user_id');

        $timeSpentHuman = [];
        foreach ($activeUsersToday as $entry) {
            $uid = $entry->user_id;
            $secs = (int) ($logoutSeconds[$uid] ?? 0);

            $ll = $latestLogin[$uid] ?? null;
            $lo = $latestLogout[$uid] ?? null;
            if ($ll && (!$lo || strtotime($ll) > strtotime($lo))) {
                // Session still open — add the time elapsed since sign-in.
                $secs += max(0, now()->timestamp - strtotime($ll));
            }

            $timeSpentHuman[$uid] = $this->formatSeconds($secs);
        }

        return view('admin.activity', compact(
            'logs', 'users', 'events', 'stats', 'activeUsersToday', 'activityCounts', 'timeSpentHuman', 'sort', 'dir'
        ));
    }

    /**
     * Format a duration in seconds as a short human string (e.g. "1h 5m").
     */
    private function formatSeconds(int $secs): string
    {
        $h = intdiv($secs, 3600);
        $m = intdiv($secs % 3600, 60);

        if ($h > 0) {
            return "{$h}h {$m}m";
        }
        if ($m > 0) {
            return "{$m}m";
        }

        return "{$secs}s";
    }

    public function clear()
    {
        ActivityLog::truncate();

        return redirect()->route('admin.activity')->with('success', 'Activity log cleared.');
    }
}
