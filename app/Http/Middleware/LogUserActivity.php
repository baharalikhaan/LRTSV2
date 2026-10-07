<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;

/**
 * Records authenticated user activity: every request is logged as a "view"
 * (GET) or an "action" (POST/PUT/PATCH/DELETE). Login/logout are captured
 * separately from the auth events. Noise endpoints are skipped.
 */
class LogUserActivity
{
    /** Path prefixes that should never be logged. */
    private const IGNORE = [
        'login',
        'logout',
        'sso',
        'Auth',
        'notifications',
        'admin/activity',
        'admin/logs',
    ];

    public function handle(Request $request, Closure $next)
    {
        // Run the request first so the authenticated user is resolved by the
        // route's auth middleware before we inspect it.
        $response = $next($request);

        try {
            $this->log($request);
        } catch (\Throwable $e) {
            // Activity logging must never break a request.
            \Log::warning('Activity log failed: ' . $e->getMessage());
        }

        return $response;
    }

    private function log(Request $request): void
    {
        $user = $request->user();
        if (!$user) {
            return;
        }

        $path = ltrim($request->path(), '/');
        foreach (self::IGNORE as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return;
            }
        }

        $method = strtoupper($request->method());
        $event = $method === 'GET' ? 'view' : 'action';

        $routeName = optional($request->route())->getName();

        ActivityLog::create([
            'user_id'     => $user->id,
            'event'       => $event,
            'description' => $routeName ?: $path,
            'method'      => $method,
            'url'         => substr($request->fullUrl(), 0, 2000),
            'route'       => $routeName,
            'ip'          => $request->ip(),
            'user_agent'  => substr((string) $request->userAgent(), 0, 255),
        ]);
    }
}
