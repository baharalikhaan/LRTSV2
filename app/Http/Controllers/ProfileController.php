<?php

namespace App\Http\Controllers;

use App\Models\Nationality;
use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $nationalities = Nationality::orderBy('name')->get();
        $colleges = College::orderBy('name')->get();
        $user = auth()->user();

        return view('profile.edit', compact('user', 'nationalities', 'colleges'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'         => 'nullable|string|max:50',
            'qu_id'         => 'nullable|string|max:100',
            'nationality_id'=> 'nullable|exists:nationalities,id',
            'college'       => 'nullable|string|max:255',
            'faculty'       => 'nullable|boolean',
            'password'      => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['faculty'] = $request->has('faculty');

        $user->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
            ]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
}
