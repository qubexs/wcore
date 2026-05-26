<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\EmailSendLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $dailyCount = EmailSendLog::whereDate('created_at', today())->count();
        if ($dailyCount >= 100) {
            return back()->withErrors(['email' => 'Daily registration limit reached (100/100). Please try again tomorrow.'])->withInput();
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $request->password,
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(15),
            'is_verified' => false,
        ]);

        try {
            $siteName = Setting::where('key', 'site_name')->value('value') ?? 'wCore HTPN';
            Mail::raw(
                "Hi {$user->name},\n\nYour verification code is: {$code}\n\nThis code expires in 15 minutes.\n\nIf you did not register for {$siteName}, please ignore this email.",
                function ($message) use ($user, $siteName) {
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
        }

        return redirect()->route('verify-code.form', ['email' => $user->email])
            ->with('success', 'Registration successful! Please check your email for the verification code.');
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
    }
}
