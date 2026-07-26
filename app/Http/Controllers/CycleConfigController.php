<?php

namespace App\Http\Controllers;

use App\Models\CycleConfig;
use Illuminate\Http\Request;

class CycleConfigController extends Controller
{
    public function index()
    {
        $cycleConfigs = CycleConfig::withCount('programs')->orderBy('year', 'desc')->orderBy('title')->get();
        return view('cycle-configs.index', compact('cycleConfigs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year'  => 'required|integer|min:2000|max:2099',
            'title' => 'required|string|max:255',
        ]);

        $cycleConfig = CycleConfig::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Cycle created successfully!',
                'cycle_config' => $cycleConfig,
            ]);
        }

        return redirect()->route('cycle-configs.index')
            ->with('success', 'Cycle created successfully.');
    }

    public function update(Request $request, $id)
    {
        $cycleConfig = CycleConfig::findOrFail($id);

        $validated = $request->validate([
            'year'  => 'required|integer|min:2000|max:2099',
            'title' => 'required|string|max:255',
        ]);

        $cycleConfig->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Cycle updated successfully!',
                'cycle_config' => $cycleConfig,
            ]);
        }

        return redirect()->route('cycle-configs.index')
            ->with('success', 'Cycle updated successfully.');
    }

    public function destroy($id)
    {
        $cycleConfig = CycleConfig::findOrFail($id);
        $cycleConfig->delete();

        return redirect()->route('cycle-configs.index')
            ->with('success', 'Cycle deleted successfully.');
    }
}
