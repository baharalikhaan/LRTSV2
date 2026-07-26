<?php

namespace App\Http\Controllers;

use App\Models\Pillar;
use Illuminate\Http\Request;

class PillarController extends Controller
{
    public function index()
    {
        $pillars = Pillar::orderBy('pillar')->get();
        return view('pillars.index', compact('pillars'));
    }

    public function create()
    {
        return view('pillars.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pillar' => 'required|string|max:255|unique:pillars,pillar',
            'subpillar' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $pillar = Pillar::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Pillar created successfully.', 'pillar' => $pillar]);
        }

        return redirect()->route('pillars.index')
            ->with('success', 'Pillar created successfully.');
    }

    public function edit(Pillar $pillar)
    {
        return view('pillars.edit', compact('pillar'));
    }

    public function update(Request $request, Pillar $pillar)
    {
        $validated = $request->validate([
            'pillar' => 'required|string|max:255|unique:pillars,pillar,' . $pillar->id,
            'subpillar' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $pillar->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Pillar updated successfully.', 'pillar' => $pillar]);
        }

        return redirect()->route('pillars.index')
            ->with('success', 'Pillar updated successfully.');
    }

    public function destroy(Pillar $pillar)
    {
        if ($pillar->users()->exists()) {
            return redirect()->route('pillars.index')
                ->with('error', 'Cannot delete pillar with assigned users.');
        }

        if ($pillar->projects()->exists()) {
            return redirect()->route('pillars.index')
                ->with('error', 'Cannot delete pillar with linked projects.');
        }

        $pillar->delete();

        return redirect()->route('pillars.index')
            ->with('success', 'Pillar deleted successfully.');
    }
}
