<?php

namespace App\Http\Controllers;

use App\Models\Nationality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $nationalities = Nationality::orderBy('name')->get();
        $user = auth()->user();

        return view('profile.edit', compact('user', 'nationalities'));
    }

    public function setNationality(Request $request)
    {
        $validated = $request->validate([
            'nationality_id' => 'required|integer|exists:nationalities,id',
        ]);

        $user = auth()->user();
        $user->update([
            'nationality_id' => (int) $validated['nationality_id'],
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nationality updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Nationality updated successfully.');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nationality_id'=> 'nullable|exists:nationalities,id',
            'faculty'       => 'nullable|boolean',
            'password'      => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Name, email, and QU ID are readonly (admin-managed) — never updated
        // from this form, even if tampered values are posted.
        unset($validated['name'], $validated['email'], $validated['qu_id']);

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
