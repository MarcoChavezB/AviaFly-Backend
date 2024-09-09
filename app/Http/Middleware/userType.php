<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class userType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, ...$userTypes)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Auth token not valid'], 401);
        }

        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['error' => 'Auth token not valid'], 401);
        }

        foreach ($userTypes as $userType) {
            if (Auth::guard('sanctum')->user()->user_type == $userType) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'Usuario no autorizado'], 401);
    }
}
