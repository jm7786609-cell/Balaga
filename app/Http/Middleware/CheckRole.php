<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'admin' && $request->is('admin/*')) {
                return $next($request);
            }

            if ($user->role === 'cashier' && $request->is('cashier/*')) {
                return $next($request);
            }

            if ($user->role === 'pharmacist' && $request->is('pharmacist/*')) {
                return $next($request);
            }

            if ($user->role === 'supplier' && $request->is('supplier/*')) {
                return $next($request);
            }

            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } else if ($user->role === 'cashier') {
                return redirect()->route('cashier.dashboard');
            } else if ($user->role === 'pharmacist') {
                return redirect()->route('pharmacist.dashboard');
            } else if ($user->role === 'supplier') {
                return redirect()->route('supplier.dashboard');
            }
        }

        // If not authenticated or user_role is not valid, redirect to login
        return redirect()->route('loginPage'); // Update with your login route name
    }
}
