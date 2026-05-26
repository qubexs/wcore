<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSetting; // Import the model we just fixed
use Illuminate\Support\Facades\Auth;

class NavModeController extends Controller
{
    /**
     * This 'store' method handles the POST request from uiglass.js
     */
    public function store(Request $request)
    {
        // 1. Validate the input
        $request->validate([
            'mode' => 'required|in:dock,sidebar'
        ]);

        // 2. Check if user is logged in
        if (Auth::check()) {
            // 3. Use the static helper from your UserSetting Model
            UserSetting::setValue(Auth::id(), 'nav_mode', $request->mode);
            
            return response()->json([
                'status'  => 'success',
                'message' => 'Navigation mode saved: ' . $request->mode
            ]);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}