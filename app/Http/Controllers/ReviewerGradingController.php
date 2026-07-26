<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Project;
use App\Models\Program;
use App\Models\ReviewerRating;
use App\Models\ProgressReportGrading;
use App\Models\FinalReportGrading;
use App\Models\College;
use App\Models\Pillar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewerGradingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Access denied. Admin only.');
            }
            return $next($request);
        });
    }

    /**
     * Show the list of reviewers for grading selection.
     */
    public function index()
    {
        // Get all users who have reviewed at least one project
        try {
            $reviewers = User::whereHas('reviewedProjects')
                ->withCount('reviewedProjects')
                ->get();
        } catch (\Exception $e) {
            $reviewers = collect();
        }

        try {
            $colleges = College::orderBy('name')->get();
        } catch (\Exception $e) {
            $colleges = collect();
        }

        try {
            $pillars  = Pillar::orderBy('pillar')->get();
        } catch (\Exception $e) {
            $pillars = collect();
        }

        return view('admin.reviewers-list', compact('reviewers', 'colleges', 'pillars'));
    }

    /**
     * Show the reviewer grading page for a specific reviewer.
     */
    public function show($id)
    {
        $user = User::with('reviewedProjects')->findOrFail($id);

        // Get all programs where this reviewer has projects
        $programs = Program::whereHas('projects.reviewers', function ($q) use ($id) {
                $q->where('user_id', $id);
            })
            ->orderBy('program_title')
            ->get(['id', 'program_title']);

        // Get total distinct projects reviewed
        $totalProjects = $user->reviewedProjects()->count();

        return view('admin.reviewer-grading', compact('user', 'programs', 'totalProjects'));
    }

    /**
     * AJAX: Get projects and ratings for a specific reviewer + program.
     */
    public function ajaxList(Request $request)
    {
        $programId = $request->get('program_id');
        $userId = $request->get('user_id');

        if (!$programId || !$userId) {
            return response()->json(['projects' => [], 'ratings' => null]);
        }

        // Get projects assigned to this reviewer for this program
        $projects = Project::where('program_id', $programId)
            ->whereHas('reviewers', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->get()
            ->map(function ($project) use ($userId) {
                $progressGrading = ProgressReportGrading::where('project_id', $project->id)->first();
                $finalGrading = FinalReportGrading::where('project_id', $project->id)->first();

                $data = [
                    'id' => $project->id,
                    'old_project_id' => $project->old_project_id ?? $project->id,
                    'title' => $project->title ?? $project->project_title,
                    'deadline' => $project->submitted_at ?? now(),
                    'proposalstatus' => 'Accepted',
                    'gradeA' => null,
                    'commentA' => null,
                    'gradeB' => null,
                    'commentB' => null,
                    'gradeC' => null,
                    'commentC' => null,
                    'gradeD' => null,
                    'commentD' => null,
                    'achievementsRating' => null,
                    'achievementsComments' => null,
                    'publicationsRating' => null,
                    'publicationsComments' => null,
                    'studentsRating' => null,
                    'studentsComments' => null,
                    'budgetRating' => null,
                    'budgetComments' => null,
                ];

                if ($progressGrading) {
                    $data['progressGrade'] = $progressGrading->grade;
                    $data['achievementsRating'] = $progressGrading->grade;
                    $data['achievementsComments'] = $progressGrading->remarks;
                    $data['publicationsRating'] = $progressGrading->grade;
                    $data['publicationsComments'] = $progressGrading->remarks;
                    $data['studentsRating'] = $progressGrading->grade;
                    $data['studentsComments'] = $progressGrading->remarks;
                    $data['budgetRating'] = $progressGrading->grade;
                    $data['budgetComments'] = $progressGrading->remarks;
                } else {
                    $data['progressGrade'] = null;
                }

                if ($finalGrading) {
                    $data['gradeA'] = $finalGrading->grade;
                    $data['commentA'] = $finalGrading->remarks;
                    $data['gradeB'] = $finalGrading->grade;
                    $data['commentB'] = $finalGrading->remarks;
                    $data['gradeC'] = $finalGrading->grade;
                    $data['commentC'] = $finalGrading->remarks;
                    $data['gradeD'] = $finalGrading->grade;
                    $data['commentD'] = $finalGrading->remarks;
                }

                return $data;
            });

        // Get existing rating for this reviewer + program
        $ratings = ReviewerRating::where('reviewer_id', $userId)
            ->where('program_id', $programId)
            ->first();

        return response()->json([
            'projects' => $projects,
            'ratings' => $ratings,
        ]);
    }

    /**
     * Save ratings for a reviewer per program.
     */
    public function saveRatings(Request $request)
    {
        $request->validate([
            'reviewer' => 'required|exists:users,id',
            'user_id' => 'required|exists:users,id',
            'program_id' => 'required|exists:programs,id',
            'conflict' => 'nullable|numeric|min:1|max:5',
            'responsiveness' => 'nullable|numeric|min:1|max:5',
            'comprehensiveness' => 'nullable|numeric|min:1|max:5',
            'no_reviewers' => 'nullable|numeric|min:1|max:5',
            'behaviour' => 'nullable|numeric|min:1|max:5',
        ]);

        ReviewerRating::updateOrCreate(
            [
                'reviewer_id' => $request->reviewer,
                'program_id' => $request->program_id,
            ],
            [
                'user_id' => $request->user_id,
                'conflict' => $request->conflict ?? 0,
                'responsiveness' => $request->responsiveness ?? 0,
                'comprehensiveness' => $request->comprehensiveness ?? 0,
                'no_reviewers' => $request->no_reviewers ?? 0,
                'behaviour' => $request->behaviour ?? 0,
            ]
        );

        return redirect()->back()->with('successrating', '<span style="color: var(--color-success);"><i class="fas fa-check-circle"></i> Ratings saved successfully!</span>');
    }

    /**
     * Show reviewer detail page.
     */
    public function reviewerDetail($id)
    {
        $user = User::with('reviewedProjects')->findOrFail($id);
        $ratings = ReviewerRating::where('reviewer_id', $id)->with('program')->get();
        $totalProjects = $user->reviewedProjects()->count();

        return view('admin.reviewer-detail', compact('user', 'ratings', 'totalProjects'));
    }
}
