<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user_identification = $request->user()->user_identification;

        if (!Cache::has($user_identification)) {
            return response()->json(['error' => 'Access Denied'], 403);
        }

        $user_role = Cache::get($user_identification);

        if (!in_array($user_role, $roles)) {
            return response()->json(['error' => 'Access Denied'], 403);
        }


        return $next($request);
    }
}
