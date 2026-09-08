<?php

namespace App\Http\Controllers\DepartmentHead;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('department-head.profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|min:9|max:11|regex:/^09[2-9][0-9]{6,8}$/',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).+$/|confirmed',
        ]);

        if ($request->filled('current_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->with('error', __('flash.incorrect_password'))->withInput();
            }
        }

        $updateData = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ];

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $updateData['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        if ($validated['password']) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('department-head.profile.edit')
            ->with('success', __('flash.profile_updated'));
    }
}
