<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GaugeSettings;
use App\Models\AiSetting;

class SystemSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                return redirect()->route('home')->with('error', 'Unauthorized.');
            }
            return $next($request);
        });
    }

    /**
     * Show unified system settings page with tabs.
     */
    public function index()
    {
        $tab = request('tab', 'gauges');

        $data = [
            'tab' => $tab,
            'gauges' => GaugeSettings::orderBy('id')->get(),
            'autoGradeVisibility' => AiSetting::get('auto_grade_visibility', '1'),
            'scores' => \App\Models\Score::orderBy('value', 'desc')->get(),
            'settings' => [
                'api_key' => AiSetting::get('api_key', ''),
                'model'   => AiSetting::get('model', 'gemini-2.5-flash'),
                'mode'    => AiSetting::get('mode', 'static'),
                'prompt'  => AiSetting::get('prompt', ''),
            ],
        ];

        return view('admin.system-settings.index', $data);
    }
}
