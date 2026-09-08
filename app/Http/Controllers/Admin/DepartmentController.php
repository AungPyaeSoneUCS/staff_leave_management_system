<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('users')
            ->with('head')
            ->paginate(10);

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $potentialHeads = User::where('role', 'department_head')
            ->whereDoesntHave('departmentHeadOf')
            ->get();

        return view('admin.departments.create', compact('potentialHeads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'code' => 'required|string|unique:departments,code',
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:users,id',
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', __('flash.department_created'));
    }

    public function edit(Department $department)
    {
        $potentialHeads = User::where('role', 'department_head')
            ->where(function ($query) use ($department) {
                $query->whereDoesntHave('departmentHeadOf')
                    ->orWhere('id', $department->head_id);
            })
            ->get();

        return view('admin.departments.edit', compact('department', 'potentialHeads'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'code' => 'required|string|unique:departments,code,'.$department->id,
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:users,id',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', __('flash.department_updated'));
    }

    public function destroy(Department $department)
    {
        if ($department->users()->exists()) {
            return back()->with('error', __('flash.cannot_delete_department'));
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', __('flash.department_deleted'));
    }
}
