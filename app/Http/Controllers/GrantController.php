<?php

namespace App\Http\Controllers;

use App\Models\Grant;
use Illuminate\Http\Request;

class GrantController extends Controller
{
    public function index()
    {
        $grants = Grant::orderBy('grant_code')->get();
        return view('grants.index', compact('grants'));
    }

    public function create()
    {
        return view('grants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grant_code' => 'required|string|max:50|unique:grants,grant_code',
            'grant_name' => 'required|string|max:255',
            'category' => 'required|in:student,regular',
            'funding_agency' => 'nullable|string|max:255',
            'max_duration_years' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
        ]);

        $grant = Grant::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Grant created successfully.', 'grant' => $grant]);
        }

        return redirect()->route('grant-types.index')
            ->with('success', 'Grant created successfully.');
    }

    public function show(Grant $grant)
    {
        $grant->load(['programs.cycleConfig', 'programs.projects.latestStatus']);
        return view('grants.show', compact('grant'));
    }

    public function edit(Grant $grant)
    {
        return view('grants.edit', compact('grant'));
    }

    public function update(Request $request, Grant $grant)
    {
        $validated = $request->validate([
            'grant_code' => 'required|string|max:50|unique:grants,grant_code,' . $grant->id,
            'grant_name' => 'required|string|max:255',
            'category' => 'required|in:student,regular',
            'funding_agency' => 'nullable|string|max:255',
            'max_duration_years' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $grant->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Grant updated successfully.', 'grant' => $grant]);
        }

        return redirect()->route('grant-types.index')
            ->with('success', 'Grant updated successfully.');
    }

    public function destroy(Grant $grant)
    {
        if ($grant->programs()->exists()) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => 'Cannot delete grant with existing cycles.'], 409);
            }
            return redirect()->route('grant-types.index')
                ->with('error', 'Cannot delete grant with existing cycles.');
        }

        $grant->delete();

        return redirect()->route('grant-types.index')
            ->with('success', 'Grant deleted successfully.');
    }
}
