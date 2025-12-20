<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePartnerActive
{
    /**
     * Handle an incoming request.
     *
     * Check if the authenticated partner user is active (not suspended).
     * If suspended, log them out and redirect to login with an error message.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the authenticated partner user
        $user = auth('partner')->user();

        // If user is authenticated and is suspended
        if ($user && $user->status === 'suspended') {
            // Log them out
            Auth::guard('partner')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect to login with error message
            return redirect()->route('partner.login')
                ->withErrors(['email' => 'Your account has been suspended. Please contact support for assistance.']);
        }

        return $next($request);
    }
}
