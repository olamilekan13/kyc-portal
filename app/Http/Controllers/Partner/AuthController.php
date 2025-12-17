<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('partner')->check()) {
            return redirect()->route('partner.dashboard');
        }

        return view('partner.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('partner')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $partner = Auth::guard('partner')->user();
            $partner->update(['last_accessed_at' => now()]);

            Log::info('Partner logged in', [
                'partner_id' => $partner->id,
                'email' => $partner->email,
            ]);

            return redirect()->intended(route('partner.dashboard'));
        }

        Log::warning('Partner login failed', [
            'email' => $request->email,
            'ip_address' => $request->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    public function logout(Request $request)
    {
        $partner = Auth::guard('partner')->user();

        Log::info('Partner logged out', [
            'partner_id' => $partner->id ?? null,
            'email' => $partner->email ?? null,
        ]);

        Auth::guard('partner')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('partner.login')->with('success', 'You have been logged out successfully.');
    }

    public function showForgotPasswordForm()
    {
        return view('partner.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('partners')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function showResetPasswordForm(Request $request, $token)
    {
        return view('partner.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('partners')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('partner.login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function showChangePasswordForm()
    {
        return view('partner.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $partner = Auth::guard('partner')->user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $partner->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        // Update password
        $partner->update([
            'password' => Hash::make($request->password),
        ]);

        Log::info('Partner password changed', [
            'partner_id' => $partner->id,
            'email' => $partner->email,
        ]);

        return redirect()->route('partner.dashboard')
            ->with('success', 'Your password has been changed successfully.');
    }
}
