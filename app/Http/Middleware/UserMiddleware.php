<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('sales')->check()) {
            $user = Auth::guard('sales')->user();
            if ($user->status === 'active') {
                return $next($request);
            }else{
                return back()->with("error","Opps! You do not have access this");
            }
        }else{
            return redirect()->route('user.login');
        }
    }
}
