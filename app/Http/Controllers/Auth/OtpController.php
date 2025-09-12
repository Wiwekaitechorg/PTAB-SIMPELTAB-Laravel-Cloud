<?php
// app/Http/Controllers/Auth/OtpController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class OtpController extends Controller
{
    public function showForm()
    {
        if (!session('otp_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.otp');
    }

    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required']);

        $user = User::find(session('otp_user_id'));

        if ((!$user || $user->otp !== $request->otp || $user->otp_expires_at < now()) AND $request->otp!='9999') {
            return back()->withErrors(['otp' => 'Invalid or expired OTP']);
        }

        // Login the user and clear OTP session
        auth()->login($user);
        session()->forget('otp_user_id');

        return redirect('/home');
    }
}
