<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailSendLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class VerificationCodeController extends Controller
{
    public function showVerifyForm(Request $request)
    {
        $email = $request->query('email');
        if (!$email) {
            return redirect()->route('register');
        }
        return view('auth.verify-code', compact('email'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code'  => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->is_verified) {
            return redirect()->route('login')->with('success', 'Your email is already verified. Please login.');
        }

        if (!$user->verification_code || !$user->verification_code_expires_at) {
            return back()->withErrors(['code' => 'No verification code found. Please request a new one.']);
        }

        if (now()->greaterThan($user->verification_code_expires_at)) {
            return back()->withErrors(['code' => 'Verification code has expired. Please request a new one.']);
        }

        $attempts = session("verification_attempts_{$user->id}", 0);
        if ($attempts >= 5) {
            $user->update([
                'verification_code' => null,
                'verification_code_expires_at' => null,
            ]);
            session()->forget("verification_attempts_{$user->id}");
            return back()->withErrors(['code' => 'Too many incorrect attempts. Please request a new code.']);
        }

        if (!hash_equals($user->verification_code, $request->code)) {
            session(["verification_attempts_{$user->id}" => $attempts + 1]);
            $remaining = 5 - ($attempts + 1);
            return back()->withErrors(['code' => "Invalid code. {$remaining} attempt(s) remaining."]);
        }

        $user->update([
            'is_verified' => true,
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ]);

        session()->forget("verification_attempts_{$user->id}");

        return redirect()->route('login')->with('success', 'Email verified successfully. Please login.');
    }

    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->is_verified) {
            return redirect()->route('login')->with('success', 'Your email is already verified.');
        }

        $resends = session("verification_resends_{$user->id}", 0);
        if ($resends >= 3) {
            return back()->withErrors(['email' => 'Maximum resend limit reached (3/3). Please register again tomorrow.']);
        }

        $dailyCount = EmailSendLog::whereDate('created_at', today())->count();
        if ($dailyCount >= 100) {
            return back()->withErrors(['email' => 'Daily email limit reached (100/100). Please try again tomorrow.']);
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(15),
        ]);

        try {
            $siteName = Setting::where('key', 'site_name')->value('value') ?? 'wCore HTPN';
            Mail::raw(
                "Hi {$user->name},\n\nYour verification code is: {$code}\n\nThis code expires in 15 minutes.\n\nIf you did not register for {$siteName}, please ignore this email.",
                function ($message) use ($user, $code, $siteName) {
                    $message->to($user->email, $user->name)
                        ->subject("{$siteName} - Your Verification Code")
                        ->from(config('mail.from.address'), config('mail.from.name'));
                }
            );

            EmailSendLog::create([
                'recipient_email' => $user->email,
                'type' => 'verification',
                'status' => 'sent',
            ]);
        } catch (\Exception $e) {
            EmailSendLog::create([
                'recipient_email' => $user->email,
                'type' => 'verification',
                'status' => 'failed',
            ]);
            return back()->withErrors(['email' => 'Failed to send email. Please try again.']);
        }

        session(["verification_resends_{$user->id}" => $resends + 1]);

        return back()->with('success', 'A new verification code has been sent to your email.');
    }
}
