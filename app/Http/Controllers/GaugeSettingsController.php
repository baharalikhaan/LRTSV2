<?php

namespace App\Http\Controllers;

use App\Models\GaugeSettings;
use App\Models\AiSetting;
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
        $autoGradeVisibility = AiSetting::get('auto_grade_visibility', '1');

        return view('admin.gauge-settings.index', compact('gauges', 'autoGradeVisibility'));
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

        return redirect()->route('admin.system-settings', ['tab' => 'gauges'])->with('success', $gaugeSetting->name . ' settings updated successfully.');
    }

    /**
     * Save auto-grade visibility setting.
     */
    public function saveAutoGradeVisibility(Request $request)
    {
        $request->validate([
            'auto_grade_visibility' => 'required|in:0,1',
        ]);

        AiSetting::set('auto_grade_visibility', $request->input('auto_grade_visibility'));

        // Detect which tab the request came from
        $referer = $request->headers->get('referer', '');
        $tab = 'gauges';
        if (str_contains($referer, 'tab=grading')) {
            $tab = 'grading';
        }

        return redirect()->route('admin.system-settings', ['tab' => $tab])->with('success', 'Auto-grade visibility updated.');
    }
}
