<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserAttribute
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->nationality === null) {
            return redirect()->route('nationality');
        }
        else
        return $next($request);
    }
}
