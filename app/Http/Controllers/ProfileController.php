<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('profile');
    }

    public function update(Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::user()->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|max:12|required_with:current_password',
            'password_confirmation' => 'nullable|min:8|max:12|required_with:new_password|same:new_password',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Basic fields
        $user->name = $request->input('name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');

        // Personal Information
        $user->salutation = $request->input('salutation');
        $user->professional_title = $request->input('professional_title');
        $user->job_title = $request->input('job_title');
        $user->bio = $request->input('bio');

        // Contact Information
        $user->phone = $request->input('phone');
        $user->phone_extension = $request->input('phone_extension');
        $user->secondary_email = $request->input('secondary_email');
        $user->mobile_phone = $request->input('mobile_phone');
        $user->fax = $request->input('fax');

        // Professional Information
        $user->specialization = $request->input('specialization');
        $user->mmc_reg_no = $request->input('mmc_reg_no');
        $user->mmc_reg_expiry = $request->input('mmc_reg_expiry') ? $request->input('mmc_reg_expiry') : null;
        $user->other_reg_no = $request->input('other_reg_no');
        $user->other_reg_expiry = $request->input('other_reg_expiry') ? $request->input('other_reg_expiry') : null;
        $user->key_credentials = $request->input('key_credentials');

        // Preferences
        $user->preferred_language = $request->input('preferred_language') ?? 'en';
        $user->timezone = $request->input('timezone') ?? 'UTC';
        $user->two_factor_enabled = $request->has('two_factor_enabled') ? 1 : 0;

        // Avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // Password change
        if (!is_null($request->input('current_password'))) {
            if (Hash::check($request->input('current_password'), $user->password)) {
                $user->password = $request->input('new_password');
            } else {
                return redirect()->back()->withInput()->withErrors(['current_password' => 'Current password is incorrect']);
            }
        }

        $user->save();

        return redirect()->route('profile')->withSuccess('Profile updated successfully.');
    }
}
