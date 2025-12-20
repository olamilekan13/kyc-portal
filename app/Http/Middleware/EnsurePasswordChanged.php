<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * Check if the authenticated partner user has changed their initial password.
     * If not, redirect them to the change password page.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the authenticated partner user
        $user = auth('partner')->user();

        // If user is authenticated and hasn't changed their password yet
        if ($user && !$user->password_changed) {
            // Allow access to change password routes and logout
            if (!$request->routeIs('partner.change-password') &&
                !$request->routeIs('partner.change-password.update') &&
                !$request->routeIs('partner.logout')) {

                // Flash a message to inform the user
                session()->flash('warning', 'For security reasons, please change your initial password before accessing other features.');

                return redirect()->route('partner.change-password');
            }
        }

        return $next($request);
    }
}
