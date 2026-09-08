<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function create(Request $request)
    {
        if ($request->session()->get('super_admin_register_verified')) {
            return view('auth.super-admin-register');
        }

        return view('auth.super-admin-register-key');
    }

    public function verifyKey(Request $request)
    {
        $secret = config('super-admin.register_secret', '');

        $request->validate([
            'key' => ['required', 'string'],
        ]);

        if ($secret === '' || ! hash_equals((string) $secret, (string) $request->input('key'))) {
            return back()->withErrors(['key' => __('auth.super_admin_invalid_key')]);
        }

        $request->session()->put('super_admin_register_verified', true);

        return redirect()->route('super-admin.register');
    }

    public function store(Request $request)
    {
        if (! $request->session()->get('super_admin_register_verified')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).+$/'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'super_admin',
            'is_active' => true,
            'require_admin_approval' => false,
        ]);

        $request->session()->forget('super_admin_register_verified');

        auth()->login($user);

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')
            ->with('success', __('flash.user_created'));
    }
}
