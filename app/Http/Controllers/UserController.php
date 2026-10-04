<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pillar;
use App\Models\College;
use App\Models\Department;
use App\Models\Nationality;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Admin access only.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $users = User::with('pillars')
            ->withCount('projects as lpi_projects_count')
            ->withCount('reviewedProjects as reviewer_projects_count')
            ->orderBy('name')->get();
        $nationalities = Nationality::orderBy('name')->get();
        $colleges = College::orderBy('name')->get();
        $departments = Department::with('college')->orderBy('name')->get();
        $pillars = Pillar::orderBy('pillar')->get();
        return view('users.index', compact('users', 'nationalities', 'colleges', 'departments', 'pillars'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'type' => 'required|string|max:50',
            'qu_id' => 'nullable|email|max:255',
            'nationality_id' => 'nullable|exists:nationalities,id',
            'department' => 'nullable|string|max:255',
            'college' => 'nullable|string|max:255',
            'faculty' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'pillar_ids' => 'nullable|array',
            'pillar_ids.*' => 'integer|exists:pillars,id',
        ]);

        // Passwords are managed via SSO / password reset — auto-generate one.
        $validated['password'] = Hash::make(\Illuminate\Support\Str::random(32));
        $validated['faculty'] = $request->has('faculty');
        $validated['is_active'] = $request->has('is_active');

        $user = User::create($validated);

        if ($request->has('pillar_ids')) {
            $user->pillars()->sync($request->pillar_ids);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'User created successfully!', 'user' => $user]);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'type' => 'required|string|max:50',
            'qu_id' => 'nullable|email|max:255',
            'nationality_id' => 'nullable|exists:nationalities,id',
            'department' => 'nullable|string|max:255',
            'college' => 'nullable|string|max:255',
            'faculty' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'pillar_ids' => 'nullable|array',
            'pillar_ids.*' => 'integer|exists:pillars,id',
        ]);

        // Passwords are managed via SSO / password reset — never changed here.
        unset($validated['password']);
        $validated['faculty'] = $request->has('faculty');
        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        if ($request->has('pillar_ids')) {
            $user->pillars()->sync($request->pillar_ids);
        } else {
            $user->pillars()->detach();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'User updated successfully!', 'user' => $user]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['message' => 'User deleted successfully!']);
        }

        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
