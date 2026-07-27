<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProgressReportGrading;
use App\Models\FinalReportGrading;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin() && !auth()->user()->isReviewer()) {
                abort(403, 'Access denied. Reviewers only.');
            }
            return $next($request);
        });
    }

    /**
     * Show all projects that have been graded or need grading.
     */
    public function gradedProjects()
    {
        $user = Auth::user();

        $cycles = \App\Models\CycleConfig::orderBy('program_title')->get();
        $cycleId = request('cycle_id');

        $query = $user->isAdmin()
            ? Project::with('lpi', 'program', 'program.grant')
            : $user->reviewedProjects()->visibleProgram()->with('lpi', 'program', 'program.grant');

        if ($cycleId) {
            $query->where('cycle_id', $cycleId);
        }

        $gradedProjects = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('grading.gradedProjects', compact('gradedProjects', 'cycles', 'cycleId'));
    }

    /**
     * Show the grading form for a specific project.
     */
    public function gradeProject($id)
    {
        $project = Project::with('lpi', 'program', 'program.grant')
            ->findOrFail($id);

        $finalDraft = FinalReportGrading::where('project_id', $id)->first();
        $progressDraft = ProgressReportGrading::where('project_id', $id)->first();

        return view('grading.grade', compact('project', 'finalDraft', 'progressDraft'));
    }

    /**
     * Show the unified full-page grading view (merged progress + final grading).
     * Replaces the old separate progressgrading and finalgrading routes.
     */
    public function grading($id)
    {
        $user = Auth::user();

        $project = Project::with([
            'lpi',
            'program',
            'program.grant',
            'commitments',
            'contributions',
            'outcomes',
            'students',
        ])->findOrFail($id);

        $finalGrading = FinalReportGrading::where('project_id', $id)->first();
        $progressGrading = ProgressReportGrading::where('project_id', $id)->first();

        $commitments = $project->commitments()->first();
        $contributions = $project->contributions()->get();
        $outcomes = $project->outcomes()->get();
        $students = $project->students()->get();

        $submissions = $project->submissions()
            ->orderBy('created_at', 'desc')
            ->get();

        $typeMappings = [
            'prototype'      => 'Prototype',
            'patent'         => 'Patent',
            'open_source'    => 'Open Source Software',
            'book'            => 'Book',
            'book_chapter'  => 'Book Chapter',
            'article'        => 'Article',
            'conference'     => 'Conference Paper',
            'masters'        => 'Masters Student',
            'phd'            => 'PhD Student',
            'undergrad'      => 'Undergraduate Student',
        ];

        return view('grading.grading-page', compact(
            'project',
            'finalGrading',
            'progressGrading',
            'commitments',
            'contributions',
            'outcomes',
            'students',
            'submissions',
            'typeMappings'
        ));
    }

    /**
     * Keep old method names as aliases for backward compatibility.
     */
    public function gradingPage($id)
    {
        return $this->grading($id);
    }

    public function finalGradingPage($id)
    {
        return $this->grading($id);
    }

    /**
     * Save the progress report grade with section-level ratings.
     */
    public function saveProgressGrade(Request $request, $id)
    {
        $saveAction = $request->input('save_action', 'submit');

        $rules = [
            'achievementsRating' => 'required|integer|between:1,5',
            'publicationsRating' => 'required|integer|between:1,5',
            'studentsRating'     => 'required|integer|between:1,5',
            'budgetRating'       => 'required|integer|between:1,5',
            'achievementsComments' => 'nullable|string|max:1200',
            'publicationsComments' => 'nullable|string|max:1200',
            'studentsComments'     => 'nullable|string|max:1200',
            'budgetComments'       => 'nullable|string|max:1200',
            'ethical'            => 'nullable|in:0,1',
            'analysis'           => 'nullable|string|max:255',
            'comments'           => 'nullable|string|max:255',
            'recommendation'     => 'nullable|string|max:255',
            'report_type'        => 'nullable|string|max:50',
        ];

        // For "draft", publish is optional; for "submit", it's required
        if ($saveAction === 'draft') {
            $rules['publish'] = 'nullable|in:accepted,rejected,reserved,pending';
        } else {
            $rules['publish'] = 'required|in:accepted,rejected,reserved,pending';
        }

        $request->validate($rules);

        $project = Project::findOrFail($id);
        $user = Auth::user();

        // Determine publish value based on save_action
        // "draft" = save as pending, "submit" = use the form's publish value
        $publishValue = $saveAction === 'draft' ? 'pending' : $request->publish;

        // Determine isAccepted from the user's actual selection (preserved even for drafts)
        $userSelection = $request->publish; // 'accepted' or 'rejected' or null
        $isAcceptedValue = $userSelection === 'accepted' ? 1 : ($userSelection === 'rejected' ? 0 : 0);

        $grading = \App\Models\ProgressReportGrading::updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $user->id],
            [
                'achievementsRating'    => $request->achievementsRating,
                'publicationsRating'    => $request->publicationsRating,
                'studentsRating'        => $request->studentsRating,
                'budgetRating'          => $request->budgetRating,
                'achievementsComments'  => $request->achievementsComments,
                'publicationsComments'  => $request->publicationsComments,
                'studentsComments'      => $request->studentsComments,
                'budgetComments'        => $request->budgetComments,
                'ethical'               => $request->has('ethical') ? 1 : 0,
                'analysis'              => $request->analysis ?? '',
                'comments'              => $request->comments ?? '',
                'recommendation'        => $request->recommendation ?? '',
                'publish'               => $publishValue,
                'report_type'           => $request->report_type ?? 'progress',
                'isAccepted'            => $isAcceptedValue,
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Progress grade saved successfully.']);
        }

        return redirect()->back()->with('success', 'Progress grade saved successfully.');
    }

    /**
     * Save the final report grade with section-level scores (gradeA-D + comments).
     */
    public function saveFinalGrade(Request $request, $id)
    {
        $saveAction = $request->input('save_action', 'submit');

        $rules = [
            'gradeA'    => 'required|numeric|between:1,5',
            'commentA'  => 'nullable|string|max:2000',
            'gradeB'    => 'required|numeric|between:1,5',
            'commentB'  => 'nullable|string|max:2000',
            'gradeC'    => 'required|numeric|between:1,5',
            'commentC'  => 'nullable|string|max:2000',
            'gradeD'    => 'required|numeric|between:1,5',
            'commentD'  => 'nullable|string|max:2000',
        ];

        // For "draft", publish is optional; for "submit", it's required
        if ($saveAction === 'draft') {
            $rules['publish'] = 'nullable|in:accepted,rejected,reserved,pending';
        } else {
            $rules['publish'] = 'required|in:accepted,rejected,reserved,pending';
        }

        $request->validate($rules);

        $project = Project::findOrFail($id);
        $user = Auth::user();

        // Determine publish value based on save_action
        $publishValue = $saveAction === 'draft' ? 'pending' : $request->publish;

        // Determine isAccepted from the user's actual selection (preserved even for drafts)
        $userSelection = $request->publish;
        $isAcceptedValue = $userSelection === 'accepted' ? 1 : ($userSelection === 'rejected' ? 0 : 0);

        $total = $request->gradeA +
                 $request->gradeB +
                 $request->gradeC +
                 $request->gradeD;

        $final = FinalReportGrading::updateOrCreate(
            ['project_id' => $project->id],
            [
                'user_id'     => $user->id,
                'gradeA'      => $request->gradeA,
                'commentA'    => $request->commentA ?? '',
                'gradeB'      => $request->gradeB,
                'commentB'    => $request->commentB ?? '',
                'gradeC'      => $request->gradeC,
                'commentC'    => $request->commentC ?? '',
                'gradeD'      => $request->gradeD,
                'commentD'    => $request->commentD ?? '',
                'total'       => $total,
                'publish'     => $publishValue,
                'isAccepted'  => $isAcceptedValue,
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Final grade saved successfully.']);
        }

        return redirect()->back()->with('success', 'Final grade submitted successfully.');
    }

    /**
     * Submit the final grade (legacy endpoint).
     */
    public function submitFinalGrade(Request $request, $id)
    {
        return $this->saveFinalGrade($request, $id);
    }

    /**
     * Submit the grade — marks the project as Graded in the workflow.
     */
    public function submitGrade(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = Auth::user();

        $isAssigned = DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            return response()->json(['success' => false, 'error' => 'You are not assigned to review this project.'], 403);
        }

        if (!$project->hasStatus(Project::STATUS_GRADED)) {
            $project->recordStatus(Project::STATUS_GRADED, [
                'triggered_by' => 'submit-grade',
            ], $user->id);
        }

        return response()->json(['success' => true, 'message' => 'Grade submitted successfully. Project marked as Graded.']);
    }
}
