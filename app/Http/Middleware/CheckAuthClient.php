<?php

namespace App\Http\Middleware;

use Closure,Auth;

class CheckAuthClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('customer')->check()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Vui lòng đăng nhập để tiếp tục.'], 401);
        }

        return redirect()->route('login')
            ->with('auth_intended', $request->fullUrl())
            ->with('error', 'Vui lòng đăng nhập để tiếp tục thanh toán.');
    }
}
