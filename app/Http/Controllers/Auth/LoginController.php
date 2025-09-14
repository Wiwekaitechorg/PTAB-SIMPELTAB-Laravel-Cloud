<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Override the default login behavior to add OTP step.
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->id != '14' && $user->id != '477') {
            // Generate OTP
            $otp                  = rand(100000, 999999);
            $user->otp            = $otp;
            $user->otp_expires_at = now()->addMinutes(5);
            $user->save();

            // Log the user out immediately until OTP is verified
            Auth::logout();

            // Store user ID in session
            session(['otp_user_id' => $user->id]);

            // Send the OTP via email
            //Mail::to($user->email)->send(new SendOtpMail($otp));

            // Redirect to OTP verification page
            return redirect()->route('otp.form')->with('status', 'OTP has been sent to your admin app.');
        } else {
            return redirect('/home');
        }
    }
}
