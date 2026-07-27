<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\Request;

class ScoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Handle both old and new schema — order by value desc, sort_order if it exists
        $scores = Score::orderBy('value', 'desc')->get();
        return view('scores.index', compact('scores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'label' => 'nullable|string|max:20',
            'value' => 'required|numeric|min:0|max:999.99',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $score = Score::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Score created successfully.', 'score' => $score]);
        }

        return redirect()->route('scores.index')->with('success', 'Score created successfully.');
    }

    public function edit(Score $score)
    {
        return view('scores.edit', compact('score'));
    }

    public function update(Request $request, Score $score)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'label' => 'nullable|string|max:20',
            'value' => 'required|numeric|min:0|max:999.99',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $score->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Score updated successfully.', 'score' => $score]);
        }

        return redirect()->route('scores.index')->with('success', 'Score updated successfully.');
    }

    public function destroy(Score $score)
    {
        $score->delete();

        return redirect()->route('scores.index')->with('success', 'Score deleted successfully.');
    }
}
