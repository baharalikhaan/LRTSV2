<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || !$user->isAdmin()) {
                return redirect()->route('home')->with('error', 'Unauthorized.');
            }
            return $next($request);
        });
    }

    /**
     * List all team members.
     */
    public function index()
    {
        $teamMembers = Team::orderBy('id')->get();

        return view('admin.teams.index', compact('teamMembers'));
    }

    /**
     * Store a new team member.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'role'         => 'nullable|string|max:255',
            'introduction' => 'nullable|string|max:1000',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:500',
            'path'         => 'nullable|string|max:500',
        ]);

        Team::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Team member added.']);
        }

        return redirect()->route('teams.index')->with('success', 'Team member added successfully.');
    }

    /**
     * Update a team member.
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'role'         => 'nullable|string|max:255',
            'introduction' => 'nullable|string|max:1000',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:500',
            'path'         => 'nullable|string|max:500',
        ]);

        $team->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Team member updated.']);
        }

        return redirect()->route('teams.index')->with('success', 'Team member updated successfully.');
    }

    /**
     * Delete a team member.
     */
    public function destroy(Team $team)
    {
        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Team member removed.');
    }
}
