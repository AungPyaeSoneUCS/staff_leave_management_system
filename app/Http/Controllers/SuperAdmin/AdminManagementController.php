<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Notifications\AdminCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminManagementController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'admin')
            ->with('department')
            ->paginate(15);

        return view('super-admin.admins.index', compact('admins'));
    }

    public function create()
    {
        $departments = Department::get();
        $positions = config('positions', []);

        return view('super-admin.admins.create', compact('departments', 'positions'));
    }

    public function store(Request $request)
    {
        $positions = config('positions', []);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).+$/',
            'role' => 'required|in:admin',
            'department_id' => 'nullable|exists:departments,id',
            'staff_id' => 'nullable|string|unique:users',
            'phone' => 'nullable|string|min:9|max:11|regex:/^09[2-9][0-9]{6,8}$/',
            'position' => 'nullable|string|in:'.implode(',', array_keys($positions)),
            'position_mm' => 'nullable|string|in:'.implode(',', array_values($positions)),
            'require_admin_approval' => 'nullable|boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        $admin = User::create($validated);

        // $admin->notify(new AdminCreatedNotification($admin));

        return redirect()->route('super-admin.admins.index')
            ->with('success', __('flash.user_created'));
    }

    public function edit(User $user)
    {
        if ($user->role !== 'admin') {
            abort(404);
        }

        $departments = Department::get();
        $positions = config('positions', []);

        return view('super-admin.admins.edit', compact('user', 'departments', 'positions'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== 'admin') {
            abort(404);
        }

        $positions = config('positions', []);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).+$/',
            'role' => 'required|in:admin',
            'department_id' => 'nullable|exists:departments,id',
            'staff_id' => 'nullable|string|unique:users,staff_id,'.$user->id,
            'phone' => 'nullable|string|min:9|max:11|regex:/^09[2-9][0-9]{6,8}$/',
            'position' => 'nullable|string|in:'.implode(',', array_keys($positions)),
            'position_mm' => 'nullable|string|in:'.implode(',', array_values($positions)),
            'is_active' => 'boolean',
            'require_admin_approval' => 'nullable|boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        $user->update($validated);

        return redirect()->route('super-admin.admins.index')
            ->with('success', __('flash.user_updated'));
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'admin') {
            abort(404);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', __('flash.cannot_delete_self'));
        }

        $user->delete();

        return redirect()->route('super-admin.admins.index')
            ->with('success', __('flash.user_deleted'));
    }

    public function toggleActive(User $user)
    {
        if ($user->role !== 'admin') {
            abort(404);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', __('flash.cannot_delete_self'));
        }

        $user->update(['is_active' => ! $user->is_active]);

        if (! $user->is_active) {
            \DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return back()->with('success', $user->is_active ? __('flash.user_activated') : __('flash.user_deactivated'));
    }
}
