<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Pillar;
use App\Models\College;
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
        $users = User::orderBy('name')->get();
        $nationalities = Nationality::orderBy('name')->get();
        return view('users.index', compact('users', 'nationalities'));
    }

    public function create()
    {
        $pillars = Pillar::all();
        $colleges = College::orderBy('name')->get();
        return view('users.create', compact('pillars', 'colleges'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'type' => 'required|string|max:50',
            'qu_id' => 'nullable|string|max:100',
            'nationality_id' => 'nullable|exists:nationalities,id',
            'phone' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:255',
            'college' => 'nullable|string|max:255',
            'faculty' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['faculty'] = $request->has('faculty');
        $validated['is_active'] = $request->has('is_active');

        $user = User::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'User created successfully!', 'user' => $user]);
        }

        return redirect()->route('users')->with('success', 'User created successfully!');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'type' => 'required|string|max:50',
            'qu_id' => 'nullable|string|max:100',
            'nationality_id' => 'nullable|exists:nationalities,id',
            'phone' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:255',
            'college' => 'nullable|string|max:255',
            'faculty' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $validated['faculty'] = $request->has('faculty');
        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'User updated successfully!', 'user' => $user]);
        }

        return redirect()->route('users')->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['message' => 'User deleted successfully!']);
        }

        return redirect()->route('users')->with('success', 'User deleted successfully!');
    }
}
