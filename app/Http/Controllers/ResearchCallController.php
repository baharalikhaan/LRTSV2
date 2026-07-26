<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;

class ResearchCallController extends Controller
{
    /**
     * Show all research calls with their visibility flag.
     */
    public function index()
    {
        $programs = Program::with(['grant', 'cycleConfig'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('research-calls.index', compact('programs'));
    }

    /**
     * Toggle the is_visible flag on a research call.
     */
    public function toggle($id)
    {
        $program = Program::findOrFail($id);

        $program->update([
            'is_visible' => !$program->is_visible,
        ]);

        $status = $program->is_visible ? 'shown' : 'hidden';

        return redirect()->route('research-calls.index')
            ->with('success', "Research call '{$program->program_title}' {$status} successfully.");
    }
}
