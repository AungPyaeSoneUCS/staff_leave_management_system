<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OTPMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors([
                'email' => __('auth.no_account_found'),
            ])->onlyInput('email');
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Session::put('password_reset_otp', $otp);
        Session::put('password_reset_otp_expires', now()->addMinutes(10));
        Session::put('password_reset_email', $user->email);

        Mail::to($user->email)->send(new OTPMail($otp));

        return redirect()->route('password.verify.otp')->with('status', __('auth.otp_sent'));
    }

    public function showVerifyOtpForm()
    {
        if (! Session::has('password_reset_otp') || ! Session::has('password_reset_email')) {
            return redirect()->route('login')->withErrors([
                'email' => __('auth.otp_session_expired'),
            ]);
        }

        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $storedOtp = Session::get('password_reset_otp');
        $expiresAt = Session::get('password_reset_otp_expires');

        if (! $storedOtp || ! $expiresAt || now()->greaterThan($expiresAt)) {
            Session::forget(['password_reset_otp', 'password_reset_otp_expires', 'password_reset_email']);

            return redirect()->route('login')->withErrors([
                'otp' => __('auth.otp_session_expired'),
            ]);
        }

        if ($request->otp !== $storedOtp) {
            return back()->withErrors([
                'otp' => __('auth.invalid_otp'),
            ]);
        }

        Session::forget('password_reset_otp');
        Session::put('password_reset_verified', true);

        return redirect()->route('password.reset.form');
    }

    public function showResetForm()
    {
        if (! Session::get('password_reset_verified')) {
            return redirect()->route('login')->withErrors([
                'email' => __('auth.otp_session_expired'),
            ]);
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        if (! Session::get('password_reset_verified')) {
            return redirect()->route('login')->withErrors([
                'email' => __('auth.otp_session_expired'),
            ]);
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = Session::get('password_reset_email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            Session::forget(['password_reset_verified', 'password_reset_email']);

            return redirect()->route('login')->withErrors([
                'email' => __('auth.no_account_found'),
            ]);
        }

        $user->password = $request->password;
        $user->save();

        Session::forget(['password_reset_verified', 'password_reset_email']);

        return redirect()->route('login')->with('status', __('auth.password_reset_success'));
    }
}
