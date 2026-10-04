<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiSetting;

class AiSettingsSeeder extends Seeder
{
    public function run(): void
    {
        AiSetting::set('api_key', env('GEMINI_API_KEY', ''));
        AiSetting::set('model', env('GEMINI_MODEL', 'gemini-2.5-flash'));
        AiSetting::set('mode', env('AI_MODE', 'static'));
        AiSetting::set('prompt', \App\Http\Controllers\AdminAiController::DEFAULT_PROMPT);
    }
}
