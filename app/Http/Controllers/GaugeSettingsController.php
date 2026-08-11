<?php

namespace App\Http\Controllers;

use App\Models\GaugeSettings;
use Illuminate\Http\Request;

class GaugeSettingsController extends Controller
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
     * Show gauge settings page.
     */
    public function index()
    {
        $gauges = GaugeSettings::orderBy('id')->get();

        return view('admin.gauge-settings.index', compact('gauges'));
    }

    /**
     * Update a gauge setting.
     */
    public function update(Request $request, GaugeSettings $gaugeSetting)
    {
        $validated = $request->validate([
            'redfrom'    => 'required|integer|min:0',
            'redto'      => 'required|integer|min:0',
            'yellowfrom' => 'required|integer|min:0',
            'yellowto'   => 'required|integer|min:0',
            'greenfrom'  => 'required|integer|min:0',
            'greento'    => 'required|integer|min:0',
        ]);

        $gaugeSetting->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Gauge settings updated.']);
        }

        return redirect()->route('gauge-settings.index')->with('success', $gaugeSetting->name . ' settings updated successfully.');
    }
}
