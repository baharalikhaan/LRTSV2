<?php

namespace App\Http\Controllers;

use App\Models\AboutUS;
use App\Models\Announcement;
use App\Models\Cycle;
use App\Models\EmailSettings;
use App\Models\GuageSettings;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DataTables;
use App\Models\Project;
use App\Models\FromConfTool;
use PhpOffice\PhpSpreadsheet\IOFactory;


class AdminProgressDetails extends Controller
{

    public function __construct() {}

    //cycle list page
    public function index(Request $request)
    {
        return view('adminProgress/cycle', []);
    }

    //list cycle ajax
    public function ajaxListCycle()
    {
        $data = DB::table('cycle')
            ->select('*')
            ->where('status', '=', 'active')
            ->get();
        return DataTables::of($data)
            ->addColumn('grant_type', function ($row) {
                $type = $row->grant_type ?? 'N/A';
                $color = $type == 'regular' ? 'primary' : ($type == 'student' ? 'success' : 'secondary');
                return '<span class="badge badge-' . $color . '">' . ucfirst($type) . '</span>';
            })
            ->addColumn('action', function ($row) {

                $url1 = route('adminProgressSummary', ['cycle' => $row->id]);
                $url2 = route('AdminProjects', ['cycle' => $row->id]);
                $url3 = route('assignReview', ['cycle' => $row->id]);

                $BtnGroup = '<div class="btn-group" role="group" aria-label="Cycle Actions">';
                $BtnGroup .= '<a href="' . $url1 . '" class="btn btn-warning btn-sm">Cycle Summary</a>';

                $BtnGroup .= '</div>';
                return    $BtnGroup;
            })
            ->rawColumns(['grant_type', 'action'])
            ->make(true);
    }

    //projects list page
    public function projects($cycle)
    {
        return view('adminProgress/projects', ['cycle' => $cycle]);
    }

    //list projects ajax
    public function ajaxListProjects($cycle)
    {
        $data = DB::table('projects')
            ->select('*')
            ->where('cycle', '=', $cycle)
            ->get();
        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $type = auth()->user()->type;
                $Btn =    null;
                $url1 = route('adminProjectDetails', ['project' => $row->id]);
                $Btn = '<a href="' . $url1 . '" class="btn btn-teal btn-sm">Progress Details</a>';
                return   $Btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    //project details
    public function projectDetails($project)
    {
        $project = DB::table('projects')
            ->select('*')
            ->where('id', '=', $project)
            ->first();
        $lpiprogress = "";
        $reviewerprogress = "";
        return view('adminProgress/projectDetails', ['project' => $project, 'lpiprogress' => $lpiprogress, 'reviewerprogress' => $reviewerprogress]);
    }


    //Progress Report 2 Extension Page
    public function pr2Extension()
    {
        $cycles = DB::table('cycle')
            ->select('id', 'cycle_title', 'grant_type')
            ->where('status', '=', 'active')
            ->get();
        return view('adminProgress.pr2Extension', ['cycles' => $cycles]);
    }

    //Progress Report 2 Extension - AJAX List
    public function ajaxListPr2Extension(Request $request)
    {
        $cycleId = $request->input('cycle');
        $grantType = $request->input('grant_type');

        $query = DB::table('projects')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->join('users', 'users.id', '=', 'projects.user_id')
            ->select(
                'projects.id',
                'projects.old_project_id',
                'projects.title',
                'projects.has_progress_report2',
                'cycle.cycle_title',
                'cycle.grant_type',
                'users.email',
                'users.name as lpi_name'
            );

        if (!empty($cycleId)) {
            $query->where('projects.cycle', '=', $cycleId);
        }
        if (!empty($grantType)) {
            $query->where('cycle.grant_type', '=', $grantType);
        }

        $data = $query->get();

        return DataTables::of($data)
            ->addColumn('extended', function ($row) {
                if ($row->has_progress_report2) {
                    return '<span class="badge badge-success">Extended</span>';
                }
                return '<span class="badge badge-secondary">Not Extended</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row->has_progress_report2) {
                    $url = route('pr2.remove.single');
                    return '<form method="POST" action="' . $url . '" style="display:inline;">
                        ' . csrf_field() . '
                        <input type="hidden" name="project_id" value="' . $row->id . '" />
                        <button type="submit" class="btn btn-danger btn-sm" style="background-color:#dc3545;border-color:#dc3545;color:white;">
                            <i class="fa fa-times"></i> Remove Extension
                        </button>
                    </form>';
                }
                $url = route('pr2.extend.single');
                return '<form method="POST" action="' . $url . '" style="display:inline;">
                    ' . csrf_field() . '
                    <input type="hidden" name="project_id" value="' . $row->id . '" />
                    <button type="submit" class="btn btn-teal btn-sm">Extend</button>
                </form>';
            })
            ->addColumn('checkbox', function ($row) {
                if (!$row->has_progress_report2) {
                    return '<input type="checkbox" name="selected_projects[]" value="' . $row->id . '" class="project-checkbox" />';
                }
                return '';
            })
            ->rawColumns(['extended', 'action', 'checkbox'])
            ->make(true);
    }

    //Extend single project for PR2
    public function pr2ExtendSingle(Request $request)
    {
        $projectId = $request->input('project_id');
        DB::table('projects')
            ->where('id', '=', $projectId)
            ->update(['has_progress_report2' => true]);

        return redirect()->back()->with('success', 'Project has been extended for Progress Report 2.');
    }

    //Remove extension from a single project
    public function pr2RemoveSingle(Request $request)
    {
        $projectId = $request->input('project_id');
        DB::table('projects')
            ->where('id', '=', $projectId)
            ->update(['has_progress_report2' => false]);

        return redirect()->back()->with('success', 'Progress Report 2 extension has been removed from the project.');
    }

    //Remove extension from multiple projects (bulk)
    public function pr2RemoveBulk(Request $request)
    {
        $projectIds = $request->input('selected_projects', []);

        if (empty($projectIds)) {
            return redirect()->back()->with('error', 'No projects selected.');
        }

        DB::table('projects')
            ->whereIn('id', $projectIds)
            ->where('has_progress_report2', '=', true)
            ->update(['has_progress_report2' => false]);

        $count = count($projectIds);
        return redirect()->back()->with('success', $count . ' project(s) have been removed from Progress Report 2 extension.');
    }

    //Extend multiple projects for PR2 (bulk)
    public function pr2ExtendBulk(Request $request)
    {
        $projectIds = $request->input('selected_projects', []);

        if (empty($projectIds)) {
            return redirect()->back()->with('error', 'No projects selected.');
        }

        DB::table('projects')
            ->whereIn('id', $projectIds)
            ->where('has_progress_report2', '=', false)
            ->update(['has_progress_report2' => true]);

        $count = count($projectIds);
        return redirect()->back()->with('success', $count . ' project(s) have been extended for Progress Report 2.');
    }

    //project details
    public function summary($cycle)
    {
        // Check the grant type for this cycle
        $cycleData = DB::table('cycle')
            ->select('id', 'cycle_title', 'grant_type')
            ->where('id', '=', $cycle)
            ->first();

        $cycleid = $cycleData->id ?? 'NA';
        $cycleTitle = $cycleData->cycle_title ?? 'NA';
        $grantType = $cycleData->grant_type ?? 'regular';

        // If student grant, use the student-specific summary
        if ($grantType === 'student') {
            $summary = DB::table('from_conf_tool as ct')
                ->select(
                    'ct.email',
                    'ct.old_project_id',
                    'p.id as project_id',
                    'p.student_project_draft',
                    'p.requested_budget_qar',
                    'p.spending',
                    'p.student_engagement',
                    'p.publications',
                    DB::raw("CASE WHEN p.id IS NOT NULL THEN 'Yes' ELSE 'No' END as registration"),
                    DB::raw("(SELECT COUNT(*) FROM studentgrant_students s WHERE s.project_id = p.id) as students_count"),
                    DB::raw("(SELECT COUNT(*) FROM studentgrant_students s WHERE s.project_id = p.id AND s.nationality = 'qatri') as qatari_count"),
                    DB::raw("(SELECT COUNT(*) FROM studentgrant_students s WHERE s.project_id = p.id AND s.nationality = 'nonqatri') as non_qatari_count")
                )
                ->leftJoin('projects as p', 'ct.old_project_id', '=', 'p.old_project_id')
                ->where('ct.cycle', '=', $cycle)
                ->get();

            return view('adminProgress/cycleSummaryStudent', [
                'summary' => $summary,
                'cycle' => $cycleTitle,
                'cycleid' => $cycleid
            ]);
        }

        // Regular grant summary
        $summary = DB::table('admin_progress')
            ->select('*')
            ->where('id', '=', $cycle)
            ->get();

        $cycleTitle = $summary[0]->cycle_title ?? 'NA';
        $cycleid = $summary[0]->id ?? 'NA';
        return view('adminProgress/cycleSummary', ['summary' => $summary, 'cycle' => $cycleTitle, 'cycleid' => $cycleid]);
    }
}
