<?php

namespace App\Http\Controllers;

use App\Models\Commitments;
use App\Models\FromConfTool;
use App\Models\Project;
use DataTables;
use Illuminate\Support\Facades\Http;
use App\Models\Projects_reviewer;
use App\Models\Projects_stakeholder;
use App\Models\Submission_files;
use App\Models\Submissions;
use App\Models\User;
use App\Models\Outcome;
use App\Models\Pillars;
use App\Models\ProjectPillar;
use App\Models\ProjectTag;
use App\Models\Tags;
use App\Models\VerifyOutcomes;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\ElseIf_;
use App\Models\ProjectAPI;
use App\Models\Cycle;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function SyncApi(REQUEST $request)
    {
        try {
            $response = Http::get('https://residence.qu.edu.qa/ords/qucust/quapi/getProjects/123$$321');
            if ($response->successful()) {
                $jsonString  = $response;
            }
        } catch (\Exception $e) {
            $jsonString = '{
                "items":[
                    {"project_num":"1007548","project_name":"QUST-1-CPH-2020-25"},
                    {"project_num":"1005974","project_name":"QUST-1-CAS-2018-31"},
                    {"project_num":"1007443","project_name":"QUST-1-CPH-2020-9"},
                    {"project_num":"1006406","project_name":"QUST-2-CMED-2018-3"},
                    {"project_num":"1005969","project_name":"QUST-1-CAS-2018-26"},
                    {"project_num":"1006647","project_name":"QUST-1-CPH-2019-4"}

                ]
            }';

            return redirect()->back()->with('SyncApi', ' Error! Cant fetch data from API');
        }

        $data = json_decode($jsonString, true);
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                ProjectAPI::updateOrCreate(
                    ['id' => $item['project_num']],
                    ['project_name' => $item['project_name']]
                );
            }
        }

        return redirect()->back()->with('SyncApi', ' Projects fetched successfully');
    }

    public function ajaxUploadProgressAdmin()
    {
        $today = Carbon::now()->toDateString();

        // $data = DB::table('projects')
        //     ->selectRaw('projects.id as proj_id,projects.old_project_id,projects.title, cycle.cycle_title,cycle.final_rpt_deadline,cycle.extended_final_rpt_deadline,cycle.prog_rpt_deadline,cycle.extended_prog_rpt_deadline,submissions.id')
        //     ->join('cycle', 'cycle.id', '=', 'projects.cycle')
        //     ->Leftjoin('submissions', 'submissions.project_id', '=', 'projects.id')
        //     ->where(function ($query) use ($today) {
        //         $query->whereNull('cycle.extended_final_rpt_deadline')
        //             ->where('cycle.final_rpt_deadline', '<', $today)
        //             ->where('submissions.id', '=', null);
        //     })
        //     ->orWhere(function ($query) use ($today) {
        //         $query->whereNotNull('cycle.extended_final_rpt_deadline')
        //             ->where('cycle.extended_final_rpt_deadline', '<', $today)
        //             ->where('submissions.id', '=', null);
        //     })
        //     ->get();



        // $data = DB::table('projects')
        //     ->selectRaw('projects.id as proj_id,projects.old_project_id,projects.title, cycle.cycle_title,cycle.final_rpt_deadline,cycle.extended_final_rpt_deadline,cycle.prog_rpt_deadline,cycle.extended_prog_rpt_deadline,submissions.id')
        //     ->join('cycle', 'cycle.id', '=', 'projects.cycle')
        //     ->Leftjoin('submissions', 'submissions.project_id', '=', 'projects.id')
        //     ->where('submissions.id', '=', null)
        //     // ->where(function ($query) use ($today) {
        //     //     $query->whereNull('cycle.extended_final_rpt_deadline')
        //     //         ->where('cycle.final_rpt_deadline', '<', $today)
        //     //         ->where('submissions.id', '=', null);
        //     // })
        //     // ->orWhere(function ($query) use ($today) {
        //     //     $query->whereNotNull('cycle.extended_final_rpt_deadline')
        //     //         ->where('cycle.extended_final_rpt_deadline', '<', $today)
        //     //         ->where('submissions.id', '=', null);
        //     // })
        //     ->get();


        $data = DB::table('projects')
            ->selectRaw('
        projects.id AS proj_id,
        projects.old_project_id,
        projects.title,
        cycle.cycle_title,
        cycle.final_rpt_deadline,
        cycle.extended_final_rpt_deadline,
        cycle.prog_rpt_deadline,
        cycle.extended_prog_rpt_deadline,

        MAX(CASE WHEN submissions.type = "Readiness" THEN 1 ELSE 0 END) AS readiness,
        MAX(CASE WHEN submissions.type = "Progress" THEN 1 ELSE 0 END) AS progress,
        MAX(CASE WHEN submissions.type = "Final" THEN 1 ELSE 0 END) AS final
    ')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->leftJoin('submissions', 'submissions.project_id', '=', 'projects.id')
            ->groupBy(
                'projects.id',
                'projects.old_project_id',
                'projects.title',
                'cycle.cycle_title',
                'cycle.final_rpt_deadline',
                'cycle.extended_final_rpt_deadline',
                'cycle.prog_rpt_deadline',
                'cycle.extended_prog_rpt_deadline'
            )
            ->get();



        return DataTables::of($data)
            ->addColumn('action', function ($row) {

                $form = '<form action="' . route('saveProgressAdmin') . '" method="POST" enctype="multipart/form-data">';
                $form .= csrf_field(); // Adding CSRF token
                $form .= '<input type="hidden" name="p_id" value=' . $row->proj_id . '>';

                // Start table
                $form .= '   <table class="table table-striped table-sm">';


                // // Progress File Input
                // $form .= '<tr>';
                // $form .= '<td><input type="file" name="progress" id="progress"></td>';
                // $form .= '<th>Progress Report</th>';


                // $form .= '</tr>';

                // // Final File Input
                // $form .= '<tr>';
                // $form .= '<td><input type="file" name="final" id="final"></td>';
                // $form .= '<th>Final Report</th>';

                // $form .= '</tr>';

                // // Final File Input
                // $form .= '<tr>';
                // $form .= '<td><input type="file" name="readiness" id="readiness"></td>';
                // $form .= '<th>Readiness Report</th>';


                // $form .= '</tr>';

                // Progress File Input
                $form .= '<tr>';
                $form .= '<td>';
                $form .= '<input type="file" name="progress" id="progress">';

                if (!empty($row->progress) && $row->progress == 1) {
                    $form .= '<br><small style="color:red;">
        Progress report already exists. If you choose a file, it will replace the existing file.
    </small>';
                }

                $form .= '</td>';
                $form .= '<th>Progress Report</th>';
                $form .= '</tr>';

                // Final File Input
                $form .= '<tr>';
                $form .= '<td>';
                $form .= '<input type="file" name="final" id="final">';

                if (!empty($row->final) && $row->final == 1) {
                    $form .= '<br><small style="color:red;">
        Final report already exists. If you choose a file, it will replace the existing file.
    </small>';
                }

                $form .= '</td>';
                $form .= '<th>Final Report</th>';
                $form .= '</tr>';

                // Readiness File Input
                $form .= '<tr>';
                $form .= '<td>';
                $form .= '<input type="file" name="readiness" id="readiness">';

                if (!empty($row->readiness) && $row->readiness == 1) {
                    $form .= '<br><small style="color:red;">
        Readiness report already exists. If you choose a file, it will replace the existing file.
    </small>';
                }

                $form .= '</td>';
                $form .= '<th>Readiness Report</th>';
                $form .= '</tr>';




                // Submit Button
                $form .= '<tr>';
                $form .= '<td></td>';
                $form .= '<td><button type="submit" class="btn btn-sm btn-teal" id="btn">Upload Report</button></td>';

                $form .= '</tr>';

                // End table
                $form .= '</table>';

                $form .= '</form>

                ';

                return $form;
            })

            ->rawColumns(['progress_report', 'final_report', 'action'])
            ->make(true);
    }

    public function saveProgressAdmin(REQUEST $request)
    {


        DB::transaction(function () use ($request) {
            $p_id = $request->input('p_id');
            $project = DB::table('projects')
                ->selectRaw('projects.*,cycle.cycle_title')
                ->join('cycle', 'cycle.id', '=', 'projects.cycle')
                ->where('projects.id', '=', $p_id)->first();


            // Storage::putFileAs('uploads/progress_reports/' . $project->cycle_title . '/', $request->file('progress'), $project->old_project_id . ".pdf");
            // Storage::putFileAs('uploads/final_reports/' . $project->cycle_title . '/', $request->file('final'), $project->old_project_id . ".pdf");

            if ($request->hasFile('progress') && $request->file('progress')->isValid()) {
                Storage::putFileAs('uploads/progress_reports/' . $project->cycle_title . '/', $request->file('progress'), $project->old_project_id . ".pdf");

                $logged_in_user_id = auth()->user()->id;
                Submissions::create([
                    "project_id" => $p_id,
                    "title" => $project->title,
                    "type" => 'Progress',
                    "user_id" => $logged_in_user_id,
                    "due_date" => Carbon::now()->toDateString()
                ]);
            }

            if ($request->hasFile('final') && $request->file('final')->isValid()) {
                Storage::putFileAs('uploads/final_reports/' . $project->cycle_title . '/', $request->file('final'), $project->old_project_id . ".pdf");
                $logged_in_user_id = auth()->user()->id;
                Submissions::create([
                    "project_id" => $p_id,
                    "title" => $project->title,
                    "type" => 'Final',
                    "user_id" => $logged_in_user_id,
                    "due_date" => Carbon::now()->toDateString()
                ]);
            }

            if ($request->hasFile('readiness') && $request->file('readiness')->isValid()) {
                Storage::putFileAs('uploads/readiness_reports/' . $project->cycle_title . '/', $request->file('readiness'), $project->old_project_id . ".pdf");
                $logged_in_user_id = auth()->user()->id;
                Submissions::create([
                    "project_id" => $p_id,
                    "title" => $project->title,
                    "type" => 'Readiness',
                    "user_id" => $logged_in_user_id,
                    "due_date" => Carbon::now()->toDateString()
                ]);
            }


            // $logged_in_user_id = auth()->user()->id;
            // Submissions::create([
            //     "project_id" => $p_id,
            //     "title" => $project->title,
            //     "type" => 'Progress',
            //     "user_id" => $logged_in_user_id,
            //     "due_date" => Carbon::now()->toDateString()
            // ]);

            // Submissions::create([
            //     "project_id" => $p_id,
            //     "title" => $project->title,
            //     "type" => 'Final',
            //     "user_id" => $logged_in_user_id,
            //     "due_date" => Carbon::now()->toDateString()
            // ]);
        });

        return redirect()->back()->with('successprogressupload', '<p style="color: green;">Reports uploaded successfully</p>');
    }

    public function newproject(REQUEST $request)
    {

        $projid_title_user = DB::table('from_conf_tool')
            ->select('from_conf_tool.id as conf_tool_id', 'title', 'from_conf_tool.old_project_id', 'users.id as userid', 'users.email', 'cycle.cycle_title', 'cycle.grant_type', 'cycle.id as cycleid')
            ->leftjoin('cycle', 'cycle.id', '=', 'from_conf_tool.cycle')
            ->leftjoin('users', 'users.email', '=', 'from_conf_tool.email')
            ->where('from_conf_tool.id', '=', $request->p_id)
            ->get()->first();


        return view('projectStep1', ['projid_title_user' => $projid_title_user]);
    }
    public function upload($p_id)
    {
        // $recordExists = Project::where('id', $p_id)->where('user_id', Auth::user()->id)->first();
        // if (!$recordExists) {
        //     return "Un-authorized";
        // }
        $project = Project::leftJoin('cycle', 'projects.cycle', '=', 'cycle.id')
            ->select('projects.*', 'cycle.upload_outcomes', 'cycle.grant_type', 'cycle.cycle_title')
            ->where('projects.id', '=', $p_id)
            ->first();


        $cycle = DB::table('cycle')
            ->select('*')
            ->where('id', '=', $project->cycle)
            ->first();

        $user = DB::table('users')
            ->select('*')
            ->where('id', '=', $project->user_id)
            ->first();

        $progress_report = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $p_id)
            ->where('submissions.type', '=', 'Progress')
            ->first();
        $progress_report2 = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $p_id)
            ->where('submissions.type', '=', 'Progress2')
            ->first();
        $final_report = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $p_id)
            ->where('submissions.type', '=', 'Final')
            ->first();

        $readiness_report = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $p_id)
            ->where('submissions.type', '=', 'Readiness')
            ->first();

        $students = DB::table('studentgrant_students')
            ->select('*')
            ->where('project_id', '=', $p_id)
            ->get();



        $outcomes = DB::table('outcomes')
            ->select('*')
            ->where('project_id', '=', $p_id)
            ->first();



        $directory = "uploads/ethical_approvals/" . $project->cycle_title . "/" . $project->old_project_id . "/";

        $files = Storage::files($directory);
        $fileNames = [];
        foreach ($files as $file) {
            $fileNames[] = basename($file);
        }


        if ($project->grant_type == 'regular')
            return view('reportUpload', ['user' => $user, 'readiness_report' => $readiness_report, 'progress_report' => $progress_report, 'progress_report2' => $progress_report2, 'final_report' => $final_report, 'p_id' => $p_id, 'project' => $project, 'outcomes' => $outcomes, 'cycle' => $cycle]);
        else
            return view('studentsGrants/reportUpload', ['fileNames' => $fileNames, 'students' => $students, 'user' => $user, 'readiness_report' => $readiness_report, 'progress_report' => $progress_report, 'progress_report2' => $progress_report2, 'final_report' => $final_report, 'p_id' => $p_id, 'project' => $project, 'outcomes' => $outcomes, 'cycle' => $cycle]);
    }

    public function index()
    {
        $reviewer = DB::table('users')
            ->select('name')
            ->where('type', '=', 'Reviewer')
            ->orwhere('type', '=', 'LPI+Reviewer')
            ->get();
        return view('projects', ['reviewer' => $reviewer]);
    }
    public function print(Request $req)
    {
        dd($req);
    }
    public function get_by_id(Request $request, $id)
    {
        $data = Project::with(['owner', 'stakeholders', 'reviewers', 'submissions.files.props'])
            ->where('id', $id)
            ->first();

        return Response::json($data);
    }
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'stakeholder' => 'required',
            'file' => 'required'

        ]);
        if (auth()->user()->type !== User::TYPE_REVIEWER) {
            DB::transaction(function () use ($request) {
                $type = auth()->user()->type;
                $path = Storage::putFile('files', $request->file('file'));
                $logged_in_user_id = auth()->user()->id;
                if ($type === User::TYPE_ADMIN)
                    $user_id = $request->stakeholder[0];
                else
                    $user_id = $logged_in_user_id;
                $project = Project::create([
                    "title" => $request->title,
                    "status" => Project::STATUS_PENDING,
                    "user_id" => $user_id
                ]);
                //if LPI is creating the project, then insert him into the Stakeholder
                //if admin is creating then this block will not be executed
                if ($type === User::TYPE_LPI || $type === User::TYPE_LPI_REVIEWER) {
                    Projects_stakeholder::create([
                        "project_id" => $project->id,
                        "user_id" => auth()->user()->id,
                    ]);
                }
                //All the other stakeholders will be added in the stakeholder table
                foreach ($request->stakeholder as $stakeholder) {
                    Projects_stakeholder::create([
                        "project_id" => $project->id,
                        "user_id" => $stakeholder,
                    ]);
                }

                $submission = Submissions::create([
                    "project_id" => $project->id,
                    "title" => $request->title,
                    "type" => Submissions::TYPE_PROPOSAL,
                    "user_id" => $logged_in_user_id,
                    "due_date" => "2023-12-12 00.00.00"
                ]);

                // TODO: Here check if File is valid otherwise ignore the file

                Submission_files::create([
                    "submission_id" => $submission->id,
                    "path" => $path,
                ]);
            });

            return view('home', ['message' => 'Projected Created Successfully']);
        } else {
            return view('home', ['message' => 'You are not authorized for this operation']);
        }
    }

    public function ajaxListgetcount($user)
    {

        // $data = Projects_reviewer::where('user_id', '=', $user)
        //     ->count();
        // return $data;

        $data = Projects_reviewer::join('projects', 'projects.id', '=', 'projects_reviewers.project_id')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->where('projects_reviewers.user_id', '=', $user)
            ->where('cycle.status', '=', 'active')
            ->count();
        return $data;
    }


    public function ajaxListreviewer(Request $request)
    {

        $data = DB::table('projects')
            ->select('cycle.id', 'pillars.pillar', 'projects.id', 'old_project_id', 'title', 'tag', 'projects.status', 'users.email')
            ->leftJoin('project_pillar', 'projects.id', '=', 'project_pillar.project_id')
            ->Join('project_tag', 'projects.id', '=', 'project_tag.project_id')
            ->Join('tags', 'project_tag.tag_id', '=', 'tags.id')
            ->join('users', 'users.id', '=', 'projects.user_id')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->leftJoin('pillars', 'pillars.id', '=', 'project_pillar.pillar_id')
            ->where('cycle.id', $request->cycle) // Ensure 'regular' is a valid value in cycle.id
            ->whereNotIn('projects.id', DB::table('projects_reviewers')->pluck('project_id'))
            ->get();

        //   dd($data);
        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('projectDetails', ['p_id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Project Details</a>';
                //    $Btn = '<button type="button" onClick="openPopup(\'' . $url . '\')" class="btn btn-teal btn-sm">Project Details</button>';
                // $Btn = '<button type="button" class="btn btn-teal btn-sm">Project Details</button>';
                return   $Btn;
            })
            ->addColumn('reviewer1', function ($row) {

                $reviewerUsers = User::join('user_pillars', 'users.id', '=', 'user_pillars.user_id')
                    ->join('pillars', 'pillars.id', '=', 'user_pillars.pillar_id')
                    ->where('users.type', 'Reviewer')
                    ->orWhere('users.type', 'LPI+Reviewer')
                    ->select('users.*', 'pillars.pillar', 'user_pillars.pillar_id')
                    ->get();

                $reviewerByPillar = $reviewerUsers->groupBy('pillar_id');
                $pillars = Pillars::all()->pluck('pillar', 'id')->toArray();

                $ctrl = '
                <td id="reference"> <select class="abc" name=A.' . $row->id . '>
                <option disabled selected>Assign Reviewer</option>';
                foreach ($reviewerByPillar as $pillarId => $reviewers) {
                    $label = $pillars[$pillarId] ?? 'Unknown Pillar';
                    $ctrl .= '<optgroup label="' . $label . '">';
                    foreach ($reviewers as $reviewer) {
                        $ctrl .=  '<option value=' . $reviewer->id . '>' . $reviewer->name . '</option>';
                    }
                    $ctrl .= '</optgroup>';
                }
                $ctrl .=  '</select><span class="oval-span" id=A.' . $row->id . '>0</span></td>';
                return   $ctrl;
            })

            ->addColumn('reviewer2', function ($row) {

                $reviewerUsers = User::join('user_pillars', 'users.id', '=', 'user_pillars.user_id')
                    ->join('pillars', 'pillars.id', '=', 'user_pillars.pillar_id')
                    ->where('users.type', 'Reviewer')
                    ->select('users.*', 'pillars.pillar', 'user_pillars.pillar_id')
                    ->get();

                $reviewerByPillar = $reviewerUsers->groupBy('pillar_id');
                $pillars = Pillars::all()->pluck('pillar', 'id')->toArray();

                $ctrl = '
            <td id="reference"> <select class="abc" name=B.' . $row->id . '>
            <option disabled selected>Assign Reviewer</option>';
                foreach ($reviewerByPillar as $pillarId => $reviewers) {
                    $label = $pillars[$pillarId] ?? 'Unknown Pillar';
                    $ctrl .= '<optgroup label="' . $label . '">';
                    foreach ($reviewers as $reviewer) {
                        $ctrl .=  '<option value=' . $reviewer->id . '>' . $reviewer->name . '</option>';
                    }
                    $ctrl .= '</optgroup>';
                }
                $ctrl .=  '</select><span class="oval-span" id=B.' . $row->id . '>0</span></td>';
                return   $ctrl;
            })

            ->rawColumns(['reviewer1', 'reviewer2', 'action'])
            ->make(true);
    }

    public function UnAssignReviewers(Request $request)
    {
        $assign = Projects_reviewer::find($request->id);
        if ($assign) {
            $assign->delete();
            return redirect()->back()->with('successbulkreviewer', '<p style="color: green;">Project un-assigned successfully</p>');
        } else {
            return redirect()->back()->with('successbulkreviewer', '<p style="color: red;">Project un-assigned successfully</p></a>');
        }
    }

    public function ajaxListAssignedReviewers()
    {
        $data = Projects_reviewer::Join('projects', 'projects_reviewers.project_id', '=', 'projects.id')
            ->join('users', 'users.id', '=', 'projects_reviewers.user_id')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->join('users as u', 'u.id', '=', 'projects.user_id')
            ->selectRaw('u.email,cycle.*,projects.*,projects_reviewers.*,users.name as reviewer')
            ->get();

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url =   $url = route('UnAssignReviewers', ['id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-danger btn-sm">Un-Assign Project</a>';
                return   $Btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function acceptProposal($r_id)
    {
        $data = Projects_reviewer::join('projects', 'projects.id', '=', 'projects_reviewers.project_id')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->join('users', 'users.id', '=', 'projects_reviewers.user_id')
            ->selectRaw('cycle.cycle_title,projects_reviewers.id,users.email,projects_reviewers.proposalstatus,projects_reviewers.project_id,projects.old_project_id')
            ->find($r_id);

        //  dd($data);
        return view('acceptProposal', ['r_id' => $r_id, 'data' => $data]);
    }

    public function acceptProposalPost(REQUEST $request)
    {

        // $rules = [
        //     'file' => 'required|file|mimes:pdf',
        // ];

        // Validate the request
        //    $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        // if ($validator->fails()) {
        //     return redirect()->back()
        //         ->withErrors($validator)
        //         ->withInput(['file1' => $request->report_type])->with(['mmtype' => $request->report_type]);
        // }

        //upload the file
        $record =   DB::table('projects_reviewers')
            ->selectRaw('cycle.cycle_title,projects.old_project_id,users.email')
            ->join('projects', 'projects.id', '=', 'projects_reviewers.project_id')
            ->join('users', 'users.id', '=', 'projects_reviewers.user_id')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->where('projects_reviewers.id', '=', $request->r_id)->first();

        if ($request->hasFile('file')) {
            $path = $request->file('file')->getRealPath();
            $file = $request->file('file');
            $file->storeAs('uploads/reviewers_agreements/' . $record->cycle_title . '/', $record->old_project_id . '-' . $record->email . '.pdf');
        }

        //update the status and set the proposal information
        DB::table('projects_reviewers')
            ->where('id', $request->r_id) // Assuming 'id' is the primary key
            ->update([
                'proposalstatus' => $request->accept,
                'statusdate' => now(),
            ]);


        //feedback to the user
        // return redirect()->back()->with(`successacceptproposal', '<p style="color: green;">Proposal submitted successfully.
        // Now you can review the project here.</p>
        // '<a onClick="showModal(\'$record->cycle_title . '/', $record->old_project_id . '-' . $record->email . '.pdf'\')" >View Agreement</a>';
        // `);

        return redirect()->back()->with('successacceptproposal', '<p style="color: green;">Agreement submitted successfully. Now you can review the project.</p>');
    }


    public function ajaxListcycle2()
    {
        $type = auth()->user()->type;
        $user = auth()->user()->id;

        if ($type == User::TYPE_ADMIN) {

            $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')
                ->select('cycle.*', DB::raw('COUNT(projects.id) as total'))->groupBy('cycle.id')->get();
        } else if ($type == User::TYPE_REVIEWER or $type == User::TYPE_LPI_REVIEWER) {
            $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')
                ->join('projects_reviewers', 'projects.id', '=', 'projects_reviewers.project_id')
                ->select('cycle.*', DB::raw('COUNT(projects.id) as total'))->groupBy('cycle.id')->where('projects_reviewers.user_id', '=', $user)->get();
        } else if ($type == User::TYPE_LPI) {
            $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')

                ->select('cycle.*', DB::raw('COUNT(projects.id) as total'))->groupBy('cycle.id')->where('user_id', '=', $user)->get();
            //  dd($data);
        }

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('gradedproject', ['c_id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">View Projects</a>';

                return   $Btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function ajaxListcycle()
    {
        $type = auth()->user()->type;
        $user = auth()->user()->id;

        if ($type == User::TYPE_ADMIN) {

            $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')
                ->select('cycle.*', DB::raw('COUNT(projects.id) as total'))->groupBy('cycle.id')->get();
        } else if ($type == User::TYPE_REVIEWER or $type == User::TYPE_LPI_REVIEWER) {
            $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')
                ->join('projects_reviewers', 'projects.id', '=', 'projects_reviewers.project_id')
                ->select('cycle.*', DB::raw('COUNT(projects.id) as total'))->groupBy('cycle.id')->where('projects_reviewers.user_id', '=', $user)->get();
        } else if ($type == User::TYPE_LPI) {
            $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')

                ->select('cycle.*', DB::raw('COUNT(projects.id) as total'))->groupBy('cycle.id')->where('user_id', '=', $user)->get();
            //  dd($data);
        }

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('project', ['c_id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Projects</a>';

                return   $Btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function conftoolProjects($cycle)
    {
        $cycle =  Cycle::find($cycle);
        return view('conftoolprojects', ['cycle' => $cycle]);
    }

    public function ajaxList22($cycle = null)
    {
        $data = DB::table('from_conf_tool')

            ->select('*')
            ->where('added', '=', '0')
            ->where('cycle', '=', $cycle)
            ->get();

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $type = auth()->user()->type;
                $Btn =    null;
                if ($row->added == 0) {
                    $url1 = route('confprojectedit', ['id' => $row->id]);
                    $Btn = '<a href="' . $url1 . '" class="btn btn-teal btn-sm">Update Project</a>';
                }
                return   $Btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    //For LPI graded projects
    public function ajaxListLPIGraded($cycle = null)
    {

        $user = auth()->user()->id;

        // $data = Project::join('final_report_grading', 'final_report_grading.project_id', '=', 'projects.id')
        //     ->Leftjoin('progress_report_grading', 'progress_report_grading.project_id', '=', 'projects.id')

        //     ->selectRaw('projects.id,projects.old_project_id,projects.title,(case when progress_report_grading.isAccepted=1 then "Accepted" else (case when progress_report_grading.isAccepted=0 then "Rejected" else "Not Set" end ) end) as status,sum(final_report_grading.total) as total, avg(final_report_grading.total) as avg')
        //     ->groupby('projects.id', 'projects.old_project_id', 'projects.title', 'progress_report_grading.isAccepted')
        //     ->where('projects.user_id', '=', $user)
        //     ->where('projects.cycle', '=', $cycle)
        //     ->get();

        $data  = DB::table('projects as p')
            ->select(
                'p.id',
                'p.old_project_id',
                'p.title',

                // Sum of the score
                DB::raw("
                    SUM(CASE
                        WHEN fin.publish = 'accepted' THEN fin.total
                        ELSE 0
                    END) AS total
                "),

                // Average of the score
                DB::raw("
                    AVG(CASE
                        WHEN fin.publish = 'accepted' THEN fin.total
                        ELSE 0
                    END) AS avg
                "),

                // Reviewer 1 status from progress report grading (if accepted)
                DB::raw("
                    CASE
                        WHEN (MAX(CASE WHEN prg.rn = 1 THEN prg.isAccepted END)) = 0 THEN 'Rejected'
                        WHEN (MAX(CASE WHEN prg.rn = 1 THEN prg.isAccepted END)) = 1 THEN 'Accepted'
                        ELSE 'Not Graded'
                    END AS `prg-Reviewer1`
                "),

                // Reviewer 2 status from progress report grading (if accepted)
                DB::raw("
                    CASE
                        WHEN (MAX(CASE WHEN prg.rn = 2 THEN prg.isAccepted END)) = 0 THEN 'Rejected'
                        WHEN (MAX(CASE WHEN prg.rn = 2 THEN prg.isAccepted END)) = 1 THEN 'Accepted'
                        ELSE 'Not Graded'
                    END AS `prg-Reviewer2`
                "),

                // Reviewer 1 status from final report grading (if accepted)
                DB::raw("
                    CASE
                        WHEN (MAX(CASE WHEN fin.rn = 1 THEN fin.isAccepted END)) = 0 THEN 'Graded'
                        WHEN (MAX(CASE WHEN fin.rn = 1 THEN fin.isAccepted END)) = 1 THEN 'Graded'
                        ELSE 'Not Graded'
                    END AS `fin-Reviewer1`
                "),

                // Reviewer 2 status from final report grading (if accepted)
                DB::raw("
                    CASE
                        WHEN (MAX(CASE WHEN fin.rn = 2 THEN fin.isAccepted END)) = 0 THEN 'Graded'
                        WHEN (MAX(CASE WHEN fin.rn = 2 THEN fin.isAccepted END)) = 1 THEN 'Graded'
                        ELSE 'Not Graded'
                    END AS `fin-Reviewer2`
                ")
            )
            ->leftJoin(DB::raw('(SELECT project_id, isAccepted, publish,
                    ROW_NUMBER() OVER (PARTITION BY project_id ORDER BY id) AS rn
                FROM progress_report_grading WHERE publish = "accepted") as prg'), 'p.id', '=', 'prg.project_id')
            ->leftJoin(DB::raw('(SELECT project_id, isAccepted, publish, total,
                    ROW_NUMBER() OVER (PARTITION BY project_id ORDER BY id) AS rn
                FROM final_report_grading WHERE publish = "accepted") as fin'), 'p.id', '=', 'fin.project_id')

            // Conditions
            ->where('p.user_id', $user)
            ->where('p.cycle', $cycle)
            ->where(function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('progress_report_grading')
                        ->whereRaw('progress_report_grading.project_id = p.id')
                        ->where('progress_report_grading.publish', 'accepted');
                })
                    ->orWhereExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('final_report_grading')
                            ->whereRaw('final_report_grading.project_id = p.id')
                            ->where('final_report_grading.publish', 'accepted');
                    });
            })

            // Grouping by project fields
            ->groupBy('p.id', 'p.old_project_id', 'p.title')
            ->get();



        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('gradingDetails', ['p_id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Report Card</a>';

                return   $Btn;
            })


            ->rawColumns(['action'])
            ->make(true);
    }



    //For reviewer graded projects
    public function ajaxListReviewerGraded($cycle = null)
    {


        $user = auth()->user()->id;

        $data = Project::join('final_report_grading', 'final_report_grading.project_id', '=', 'projects.id')
            ->Leftjoin('progress_report_grading', 'progress_report_grading.project_id', '=', 'projects.id')
            ->Leftjoin('projects_reviewers', 'projects_reviewers.project_id', '=', 'projects.id')
            ->selectRaw('projects.id,projects.old_project_id,projects.title,(case when progress_report_grading.isAccepted=1 then "Accepted" else (case when progress_report_grading.isAccepted=0 then "Rejected" else "Not Set" end ) end) as status,sum(final_report_grading.total) as total, avg(final_report_grading.total) as avg')
            ->groupby('projects.id', 'projects.old_project_id', 'projects.title', 'progress_report_grading.isAccepted')
            ->where('projects_reviewers.user_id', '=', $user)
            ->where('projects.cycle', '=', $cycle)
            ->get();



        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('gradingDetails', ['p_id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Report Card</a>';

                return   $Btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function ajaxListcycleAssignView()
    {
        $type = auth()->user()->type;
        $user = auth()->user()->id;
        if ($type == User::TYPE_ADMIN) {
            $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')
                ->select('cycle.*')->distinct()->get();
        } else if ($type == User::TYPE_REVIEWER or $type == User::TYPE_LPI_REVIEWER) {
            $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')
                ->join('projects_reviewers', 'projects.id', '=', 'projects_reviewers.project_id')
                ->select('cycle.*')->distinct()->where('projects_reviewers.user_id', '=', $user)->get();
        } else if ($type == User::TYPE_LPI) {
            $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')

                ->select('cycle.*')->distinct()->where('user_id', '=', $user)->get();
            //  dd($data);
        }

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('assignReview', ['cycle' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Projects</a>';

                return   $Btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function confprojectadd($cycle)
    {
        $cycle = Cycle::find($cycle);
        return view('confprojectadd', ['cycle' => $cycle]);
    }


    public function confprojectsave(Request $request)
    {

        if (auth()->user()->type == User::TYPE_ADMIN) {
            $cycle = Cycle::find($request->cycle);

            FromConfTool::create([
                "old_project_id" => $request->old_project_id,
                "title" => $request->title,
                "cycle" => $request->cycle,
                'author' => $request->email,
                'email' => $request->email,
                'pillars' => $request->pillars,
                'tags' => $request->tags
            ]);

            Storage::putFileAs('uploads/lpi_project_proposals/' . $cycle->cycle_title . '/', $request->file('proposal'),  $request->get('old_project_id') . ".pdf");
        }

        return redirect()->route('conftoolProjects', ['cycle' =>  $request->get('cycle')]);
    }
    public function confprojectedit($id)
    {
        $project = FromConfTool::find($id);
        return view('confprojectedit', ['project' => $project]);
    }


    public function confprojectupdate(Request $request)
    {

        if (auth()->user()->type == User::TYPE_ADMIN) {
            $project_id = $request->get('id');
            $project = FromConfTool::find($project_id);
            $project->title = $request->get('title');
            $cycle = Cycle::find($project->cycle);
            Storage::putFileAs('uploads/lpi_project_proposals/' . $cycle->cycle_title . '/', $request->file('proposal'),  $request->get('old_project_id') . ".pdf");



            $project->save();
        }

        return redirect()->route('conftoolProjects', ['cycle' =>  $request->get('cycle')]);
    }
    public function ajaxList($cycle)
    {

        $type = auth()->user()->type;
        $user = auth()->user()->id;
        if ($cycle === "null") {

            if ($type == User::TYPE_ADMIN) {

                $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')
                    ->Leftjoin('users', 'users.id', '=', 'projects.user_id')
                    ->join('project_tag', 'projects.id', '=', 'project_tag.project_id')
                    ->join('tags', 'tags.id', '=', 'project_tag.tag_id')
                    ->select('projects.id', 'tags.tag', 'cycle.cycle_title', 'cycle.final_rpt_deadline', 'users.email')
                    ->where('cycle', '=', $cycle)
                    ->get();
            } else {
                //    $data = Project::select('projects.*')->join('cycle', 'cycle.id', '=', 'projects.cycle')->selectRaw('projects.*,cycle.cycle_title,cycle.final_rpt_deadline')
                $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')
                    ->Leftjoin('users', 'users.id', '=', 'projects.user_id')
                    ->join('project_tag', 'projects.id', '=', 'project_tag.project_id')
                    ->join('tags', 'tags.id', '=', 'project_tag.tag_id')
                    ->select('projects.*', 'tags.tag', 'cycle.cycle_title', 'cycle.final_rpt_deadline', 'users.email')
                    ->where('user_id', '=', $user)
                    ->where('cycle', '=', $cycle)
                    ->get();
            }
        } else {

            if ($type == User::TYPE_ADMIN) {

                $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')
                    ->Leftjoin('users', 'users.id', '=', 'projects.user_id')
                    ->join('project_tag', 'projects.id', '=', 'project_tag.project_id')
                    ->join('tags', 'tags.id', '=', 'project_tag.tag_id')
                    ->select('projects.*', 'tags.tag', 'cycle.cycle_title', 'cycle.final_rpt_deadline', 'users.email')
                    ->where('cycle', '=', $cycle)
                    ->get();
            } else {
                $data = Project::join('cycle', 'cycle.id', '=', 'projects.cycle')
                    ->Leftjoin('users', 'users.id', '=', 'projects.user_id')
                    ->join('project_tag', 'projects.id', '=', 'project_tag.project_id')
                    ->join('tags', 'tags.id', '=', 'project_tag.tag_id')
                    ->select('projects.*', 'tags.tag', 'cycle.cycle_title', 'cycle.final_rpt_deadline', 'users.email')
                    ->where('cycle', '=', $cycle)
                    ->where('user_id', '=', $user)->get();
            }
        }

        return DataTables::of($data)
            ->addColumn('action', function ($row) {

                $type = auth()->user()->type;
                $Btn1 = $Btn2 = $Btn3 = $Btn4 = $Btn5 = null;
                $report_Exists = DB::table('outcomes')->where('project_id', '=', $row->id)->first();
                $cycle = DB::table('cycle')->where('id', '=', $row->cycle)->first();


                if ($cycle->status != 'finish') {

                    if ($type == User::TYPE_LPI) {

                        if ($report_Exists) {
                            $Btn1 = '<span style="color:green">Final Report has been submitted </span>';

                            //      if (now() > $row->final_rpt_deadline) {
                            //    } else {
                            $url1 = route('upload', ['p_id' => $row->id]);
                            $Btn1 .= '<a href="' . $url1 . '" class="btn btn-teal btn-sm">Update Progress</a>';
                            //  }
                        } else {
                            //  if (now() > $row->final_rpt_deadline) {
                            //  $Btn1 = '<span style="color:red">Deadline passed.</span>';
                            //} else {
                            $url1 = route('upload', ['p_id' => $row->id]);
                            $Btn1 = '<a href="' . $url1 . '" class="btn btn-teal btn-sm">Add Progress</a>';
                            //}
                        }
                    }




                    //     $Btn4 = '<button id="ttb" data-toggle="modal" data-mydata="44" class="btn btn-teal btn-sm">Budget</button>';
                    // $Btn4 = '<button onClick="ttb(' . $row->id . ')" class="btn btn-teal btn-sm">Budget</button>';
                    else if ($type == User::TYPE_ADMIN) {
                        $report_Exists2 = DB::table('final_report_grading')->where('project_id', '=', $row->id)->where('publish', '=', 'accepted')->first();
                        if ($report_Exists2) {
                            $Btn1 = '<span style="color:green">Project Reviewed</span>';
                        } else {
                            $url2 = route('grading', ['p_id' => $row->id]);
                            $Btn2 = '<a href="' . $url2 . '" class="btn btn-teal btn-sm">Review</a>';
                            // if (now() > $row->final_rpt_deadline) {
                            //     $Btn1 = '<span style="color:red">Deadline passed.</span>';
                            //     $url1 = route('upload', ['p_id' => $row->id]);
                            //            $Btn1 .= '<a href="' . $url1 . '" class="btn btn-teal btn-sm">Add Progress</a>';


                            // } else {
                            //     // $url1 = route('upload', ['p_id' => $row->id]);
                            //     // $Btn1 = '<a href="' . $url1 . '" class="btn btn-teal btn-sm">Add Progress</a>';
                            // }
                        }




                        $url5 = route('grading', ['p_id' => $row->id]);
                        $Btn5 = '<a href="' . $url5 . '" class="btn btn-warning btn-sm">Update Status</a>';

                        // $url3 = route('projectDetails', ['p_id' => $row->id]);
                        // $Btn3 = '<a href="' . $url3 . '" class="btn btn-teal btn-sm">Details</a>';
                    } else if ($type == User::TYPE_REVIEWER or $type = User::TYPE_LPI_REVIEWER) {
                        $url2 = route('grading', ['p_id' => $row->id]);
                        $Btn2 = '<a href="' . $url2 . '" class="btn btn-teal btn-sm">Review</a>';
                    }

                    $url3 = route('projectDetails', ['p_id' => $row->id]);
                    $Btn3 = '<a href="' . $url3 . '" class="btn btn-teal btn-sm">Details</a>';
                } else {
                    $url3 = route('projectDetails', ['p_id' => $row->id]);
                    $Btn3 = '<a href="' . $url3 . '" class="btn btn-teal btn-sm">Details</a>';
                }
                // Concatenate the buttons
                $buttons = $Btn1 . ' ' . $Btn2 . ' ' . $Btn3 . ' ' . $Btn4; //. ' ' . $Btn5;
                return   $buttons;
            })


            ->rawColumns(['action'])
            ->make(true);
    }

    public function ajaxList2($cycle)
    {

        $user = auth()->user()->id;
        $type = auth()->user()->type;
        if ($cycle === "null") {
            if ($type == User::TYPE_ADMIN) {
                $data = Project::join('projects_reviewers', 'projects_reviewers.project_id', '=', 'projects.id')
                    ->join('cycle', 'projects.cycle', '=', 'cycle.id')
                    ->join('project_tag', 'projects.id', '=', 'project_tag.project_id')
                    ->join('tags', 'tags.id', '=', 'project_tag.tag_id')
                    ->join('users', 'users.id', '=', 'projects.user_id')
                    ->selectRaw(' users.email, DATE_ADD(projects_reviewers.created_at, INTERVAL 2 WEEK) as duedate,projects_reviewers.proposalstatus,projects_reviewers.id as r_id,projects.id,projects.title,tagtitle,cycle.cycle_title,projects.old_project_id')
                    ->get();
            } else {
                $data = Project::join('projects_reviewers', 'projects_reviewers.project_id', '=', 'projects.id')
                    ->join('cycle', 'projects.cycle', '=', 'cycle.id')
                    ->join('project_tag', 'projects.id', '=', 'project_tag.project_id')
                    ->join('tags', 'tags.id', '=', 'project_tag.tag_id')
                    ->join('users', 'users.id', '=', 'projects.user_id')
                    ->selectRaw('users.email, DATE_ADD(projects_reviewers.created_at, INTERVAL 2 WEEK) as duedate,projects_reviewers.proposalstatus,projects_reviewers.id as r_id,projects.id,projects.title,tagtitle,cycle.cycle_title,projects.old_project_id')
                    ->where('projects_reviewers.user_id', '=', $user)
                    //    ->where('projects.status', '=', 'Accepted')
                    ->get();
            }
        } else {

            if ($type == User::TYPE_ADMIN) {
                $data = Project::join('projects_reviewers', 'projects_reviewers.project_id', '=', 'projects.id')
                    ->join('cycle', 'projects.cycle', '=', 'cycle.id')
                    ->join('project_tag', 'projects.id', '=', 'project_tag.project_id')
                    ->join('tags', 'tags.id', '=', 'project_tag.tag_id')
                    ->join('users', 'users.id', '=', 'projects.user_id')
                    ->selectRaw(' users.email,DATE_ADD(projects_reviewers.created_at, INTERVAL 2 WEEK) as duedate,tagtitle, projects_reviewers.proposalstatus,projects_reviewers.id as r_id,projects.id,projects.title,cycle.cycle_title,projects.old_project_id')
                    ->where('cycle', '=', $cycle)
                    ->get();
            } else {
                $data = Project::join('projects_reviewers', 'projects_reviewers.project_id', '=', 'projects.id')
                    ->join('cycle', 'projects.cycle', '=', 'cycle.id')
                    ->join('project_tag', 'projects.id', '=', 'project_tag.project_id')
                    ->join('tags', 'tags.id', '=', 'project_tag.tag_id')
                    ->join('users', 'users.id', '=', 'projects.user_id')
                    ->selectRaw(' users.email,DATE_ADD(projects_reviewers.created_at, INTERVAL 2 WEEK) as duedate,tagtitle,projects_reviewers.proposalstatus,projects_reviewers.id as r_id,projects.id,projects.title,cycle.cycle_title,projects.old_project_id')
                    ->where('projects_reviewers.user_id', '=', $user)
                    //   ->where('projects.status', '=', 'Accepted')
                    ->where('cycle', '=', $cycle)
                    ->get();
            }
        }



        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $type = auth()->user()->type;
                $Btn1 = $Btn2 = $Btn3 = null;
                if ($type == User::TYPE_LPI) {
                    $url1 = route('upload', ['p_id' => $row->id]);
                    $Btn1 = '<a href="' . $url1 . '" class="btn btn-teal btn-sm">Add Progress</a>';
                } else if ($type == User::TYPE_ADMIN) {
                    $url3 = route('projectDetails', ['p_id' => $row->id]);
                    $Btn3 = '<a href="' . $url3 . '" class="btn btn-teal btn-sm">Details</a>';
                    $url2 = route('grading', ['p_id' => $row->id]);
                    $Btn2 = '<a href="' . $url2 . '" class="btn btn-teal btn-sm">Review</a>';


                    $url22 = route('acceptProposal', ['r_id' => $row->r_id]);
                    $Btn22 = '<a href="' . $url22 . '" class="btn btn-teal btn-sm">Accept Agrement</a>';
                } else if ($type == User::TYPE_REVIEWER or $type = User::TYPE_LPI_REVIEWER) {
                    $url2 = route('grading', ['p_id' => $row->id]);
                    $Btn2 = '<a href="' . $url2 . '" class="btn btn-teal btn-sm">Review</a>';

                    $url22 = route('acceptProposal', ['r_id' => $row->r_id]);
                    $Btn22 = ' <a href="' . $url22 . '" class="btn btn-teal btn-sm">Accept Agrement</a>';
                }
                if ($row->proposalstatus == 'Accepted') {
                    $Btn22 = null;
                } else if ($row->proposalstatus == 'Rejected') {
                    $Btn22 = null;
                    $Btn2 = null;
                } else {
                    $Btn2 = null;
                }
                // Concatenate the buttons
                $buttons = $Btn1 . ' ' . $Btn2 .   $Btn22 . ' ' . $Btn3;
                return   $buttons;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function displaycycle()
    {
        $type = auth()->user()->type;
        return view('Cycleprojects', ['type' => $type]);
    }

    public function displaygradedcycle()
    {
        $type = auth()->user()->type;
        return view('Cyclegradedprojects', ['type' => $type]);
    }


    public function gradeddisplay($cycle = null)
    {
        $type = auth()->user()->type;
        $cycles = Cycle::join('projects', 'projects.cycle', '=', 'cycle.id')
            ->select('cycle.*', DB::raw('COUNT(projects.id) as total'))
            ->where('cycle.id', '=', $cycle)
            ->first();
        return view('gradedProject', ['type' => $type, 'cycle' => $cycle, 'cycles' => $cycles]);
    }

    public function display($cycle = null)
    {
        $type = auth()->user()->type;
        $cycles = Cycle::join('projects', 'projects.cycle', '=', 'cycle.id')
            ->select('cycle.*', DB::raw('COUNT(projects.id) as total'))
            ->where('cycle.id', '=', $cycle)
            ->first();

        return view('Projects', ['type' => $type, 'cycle' => $cycle, 'cycles' => $cycles]);
        /*
        if ($type === User::TYPE_ADMIN) {
            $data = Project::all();
            // dd($data);
            $reviewer = DB::table('users')
                ->select('name', 'email')
                ->where('type', '=', 'Reviewer')
                ->orWhere('type', '=', 'LPI+Reviewer')
                ->get();

            return view('projects', ['type' => $type, 'projects' => $data, 'reviewer' => $reviewer, 'permit' => 'yes']);
        } else {
            $id = auth()->user()->id;
            // $data = Project::leftJoin('projects_stakeholders', 'projects_stakeholders.project_id', '=', 'projects.id')
            //     ->select('projects_stakeholders.*', 'projects.*')
            //     ->where('projects_stakeholders.user_id', '=', $id)
            //     ->get();

            $data = Project::leftJoin('projects_stakeholders', 'projects_stakeholders.project_id', '=', 'projects.id')
                ->select('projects_stakeholders.*', 'projects.*')
                ->where('projects_stakeholders.user_id', '=', $id)
                ->get();

            $reviewerProjects = Project::leftJoin('projects_reviewers', 'projects_reviewers.project_id', '=', 'projects.id')
                ->select('projects_reviewers.*', 'projects.*')
                ->where('projects_reviewers.user_id', '=', $id)
                ->where('projects.status', '=', 'Accepted')
                ->get();
            $data = json_decode($data, true);
            //dd($data);
            return view('userProject', ['type' => $type, 'projects' => $data, 'reviewerProject' => $reviewerProjects]);
        }
        */
    }
    public function searchByProject(REQUEST $request)
    {
        $type = auth()->user()->type;
        if ($type === User::TYPE_ADMIN) {
            $data =  DB::table('projects')
                ->select('*')
                ->where('title', 'like', '%' . $request->search . '%')
                ->get();
            $data = json_decode($data, true);
            return view('projects', ['type' => $type, 'projects' => $data, 'permit' => 'yes']);
        }
    }
    public function sortBytitle()
    {
        $type = auth()->user()->type;
        if ($type === User::TYPE_ADMIN) {
            $data = DB::table('projects')
                ->select('*')
                ->orderBy('title')
                ->get();
            $data = json_decode($data, true);

            return view('projects', ['type' => $type, 'projects' => $data, 'permit' => 'yes']);
        } else {
            $id = auth()->user()->id;
            $data = Project::leftJoin('projects_stakeholders', 'projects_stakeholders.project_id', '=', 'projects.id')
                ->select('projects_stakeholders.*', 'projects.*')
                ->where('projects_stakeholders.user_id', '=', $id)
                ->get();
            $reviewerProjects = Project::leftJoin('projects_reviewers', 'projects_reviewers.project_id', '=', 'projects.id')
                ->select('projects_reviewers.*', 'projects.*')
                ->where('projects_reviewers.user_id', '=', $id)
                ->where('projects.status', '=', 'Accepted')
                ->get();
            $data = json_decode($data, true);
            return view('userProject', ['type' => $type, 'projects' => $data, 'reviewerProject' => $reviewerProjects]);
        }
    }

    public function update_status(Request $request)
    {
        if (auth()->user()->type == User::TYPE_ADMIN) {

            $project_id = $request->get('project_id');

            $project = project::find($project_id);

            if ($project->status != Project::STATUS_PENDING) {
                return Response::error("Project status is not pending");
            }
            $project->status = $request->get('status');
            if ($project->status == Project::STATUS_ACCEPTED) {
                $project->year = Carbon::now()->year;
                $project->expiry = Carbon::now()->addYears(2);
            }
            //use $uid for notification
            $project->save();
        }
        return Response::ok("Project status updated");
    }

    public function get_stakeholders(Request $request)
    {
        $stakeholders = User::whereIn('type', [User::TYPE_LPI, User::TYPE_LPI_REVIEWER])
            ->select(['id', 'name'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name
                ];
            });
        return Response::json($stakeholders);
    }

    public function get_reviewers(Request $request)
    {
        $stakeholders = User::whereIn('type', [User::TYPE_REVIEWER, User::TYPE_LPI_REVIEWER])
            ->select(['id', 'name'])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name
                ];
            });
        return Response::json($stakeholders);
    }
    public function proposal($p_id)
    {
        if (auth()->user()->type == User::TYPE_ADMIN) {
            $permit = "yes";
        } else $permit = "no";
        $project = DB::table('projects')
            ->select('*')
            ->where('id', '=', $p_id)
            ->first();
        $proposal_report = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $p_id)
            ->where('submissions.type', '=', 'Proposal')
            ->first();
        return view('proposal', ['report' => $proposal_report, 'file' => '/serveFile', 'project' => $project, 'p_id' => $p_id, 'permit' => $permit]);
    }
    public function grading($p_id)
    {
        $id = auth()->user()->id;
        $next = DB::table('projects')
            ->select('projects.id', 'final_report_grading.publish')
            ->Leftjoin('final_report_grading', 'final_report_grading.project_id', '=', 'projects.id')
            ->where('projects.id', '<>', $p_id)
            ->where('projects.spending', '<>', "NULL")
            ->where(function ($query) {
                $query->where('final_report_grading.publish', '<>', 'accepted')
                    ->orWhereNull('final_report_grading.publish');
            })
            ->first();

        // $next = DB::table('projects')
        //     ->select('projects.id', 'frg.publish')
        //     ->leftJoin('final_report_grading as frg', function ($join) {
        //         $join->on('frg.project_id', '=', 'projects.id')
        //             ->where('frg.publish', '<>', 'accepted');   // only keep “accepted” rows
        //     })
        //     ->where('projects.id', '<>', $p_id)
        //     ->whereNotNull('projects.spending')           // cleaner than  '<>' "NULL"
        //     ->where('projects.id', '<>', $p_id)
        //     ->first();
        $verify_outcomes = VerifyOutcomes::leftJoin('outcomes', 'outcomes.id', '=', 'verify_outcome.outcome_id')
            ->select('verify_outcome.*', 'outcomes.identifier', 'outcomes.type')
            ->where('verify_outcome.project_id', '=', $p_id)
            ->where('verify_outcome.user_id', '=', $id)
            ->get();
        $project = DB::table('projects')
            ->selectRaw('projects.*,cycle.cycle_title,cycle.grant_type,users.*')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->join('users', 'users.id', '=', 'projects.user_id')
            ->where('projects.id', '=', $p_id)
            ->first();
        $progressDraft = DB::table('progress_grading_draft')
            ->select('*')
            ->where('project_id', '=', $p_id)
            ->where('user_id', '=', $id)
            ->latest('created_at')
            ->first();
        // dd($progressDraft);
        $finalDraft = DB::table('final_grading_draft')
            ->select('*')
            ->where('user_id', '=', $id)
            ->where('project_id', '=', $p_id)
            ->latest('created_at')
            ->first();
        $outcomes = Outcome::leftJoin('publication_detail', 'outcomes.id', '=', 'publication_detail.outcome_id')
            ->select('outcomes.id', 'outcomes.identifier', 'outcomes.score', 'publication_detail.*')
            ->where('project_id', '=', $p_id)
            ->get();

        $outcomes2 = $project && $project->publications ? json_decode($project->publications) : [];

        $commitments = DB::table('commitments')
            ->select('*')
            ->where('project_id', '=', $p_id)
            ->first();
        $finalGrades = DB::table('final_report_grading')
            ->select('cycle.grant_type', 'final_report_grading.*', 'submissions.created_at as final_report_date')
            ->leftJoin(DB::raw('(SELECT * FROM submissions AS s1 WHERE s1.type = "Final" AND s1.id = (SELECT MAX(s2.id) FROM submissions AS s2 WHERE s2.project_id = s1.project_id)) as submissions'), function ($join) {
                $join->on('final_report_grading.project_id', '=', 'submissions.project_id');
            })
            ->LeftJoin("projects", "projects.id", "=", "final_report_grading.project_id")
            ->leftJoin("cycle", "cycle.id", "=", "projects.cycle")
            ->where('final_report_grading.user_id', '=', $id)
            ->where('final_report_grading.project_id', '=', $p_id)
            ->first();

        $readiness = DB::table('submissions')
            ->where('project_id', $p_id)  // Filter by project_id
            ->where('type', 'Readiness')
            ->orderBy('id', 'desc')       // Sort by id in descending order
            ->first();

        $progressComments = DB::table('progress_report_grading')
            ->join('ratings as rt1', 'progress_report_grading.achievementsRating', '=', 'rt1.id')
            ->join('ratings as rt2', 'progress_report_grading.publicationsRating', '=', 'rt2.id')
            ->join('ratings as rt3', 'progress_report_grading.studentsRating', '=', 'rt3.id')
            ->join('ratings as rt4', 'progress_report_grading.budgetRating', '=', 'rt4.id')

            ->leftJoin(DB::raw('(SELECT * FROM submissions AS s1 WHERE s1.type = "Progress" AND s1.id = (SELECT MAX(s2.id) FROM submissions AS s2 WHERE s2.project_id = s1.project_id)) as submissions'), function ($join) {
                $join->on('progress_report_grading.project_id', '=', 'submissions.project_id');
            })
            ->selectRaw('submissions.created_at as progress_report_date,progress_report_grading.*,rt1.rating as rt1,rt2.rating as rt2,rt3.rating as rt3, rt4.rating as rt4, (case when progress_report_grading.isAccepted=1 then "Accepted" else "Rejected" end) as acceptance')
            ->where('progress_report_grading.user_id', '=', $id)
            ->where('progress_report_grading.project_id', '=', $p_id)
            ->where(function ($q) {
                $q->where('progress_report_grading.report_type', '=', 'Progress')
                    ->orWhereNull('progress_report_grading.report_type');
            })
            ->first();

        $progressComments2 = DB::table('progress_report_grading')
            ->join('ratings as rt1', 'progress_report_grading.achievementsRating', '=', 'rt1.id')
            ->join('ratings as rt2', 'progress_report_grading.publicationsRating', '=', 'rt2.id')
            ->join('ratings as rt3', 'progress_report_grading.studentsRating', '=', 'rt3.id')
            ->join('ratings as rt4', 'progress_report_grading.budgetRating', '=', 'rt4.id')

            ->leftJoin(DB::raw('(SELECT * FROM submissions AS s1 WHERE s1.type = "Progress2" AND s1.id = (SELECT MAX(s2.id) FROM submissions AS s2 WHERE s2.project_id = s1.project_id)) as submissions'), function ($join) {
                $join->on('progress_report_grading.project_id', '=', 'submissions.project_id');
            })
            ->selectRaw('submissions.created_at as progress_report_date,progress_report_grading.*,rt1.rating as rt1,rt2.rating as rt2,rt3.rating as rt3, rt4.rating as rt4, (case when progress_report_grading.isAccepted=1 then "Accepted" else "Rejected" end) as acceptance')
            ->where('progress_report_grading.user_id', '=', $id)
            ->where('progress_report_grading.project_id', '=', $p_id)
            ->where('progress_report_grading.report_type', '=', 'Progress2')
            ->first();
        //     dd($progressComments);
        $proposal_report = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $p_id)
            ->where('submissions.type', '=', 'Proposal')
            ->orderBy('submissions.id', 'desc')
            ->first();

        $progress_report = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $p_id)
            ->where('submissions.type', '=', 'Progress')
            ->orderBy('submission_files.created_at', 'desc')
            ->first();

        $progress_report2 = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $p_id)
            ->where('submissions.type', '=', 'Progress2')
            ->orderBy('submission_files.created_at', 'desc')
            ->first();
        $final_report = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $p_id)
            ->where('submissions.type', '=', 'Final')
            ->orderBy('submission_files.created_at', 'desc')
            ->first();

        $readiness_report = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $p_id)
            ->where('submissions.type', '=', 'Readiness')
            ->orderBy('submission_files.created_at', 'desc')
            ->first();

        $outcomes = json_decode($outcomes, true);
        $verify_outcomes = json_decode($verify_outcomes, true);
        if (auth()->user()->type == User::TYPE_ADMIN) {
            $permit = "yes";
        } else $permit = "no";
        $contributions = DB::table('contribution')
            ->select('*')
            ->where('project_id', '=', $p_id)
            ->get();

        $students = DB::table('attached_students')
            ->select('*')
            ->where('project_id', '=', $p_id)
            ->get();

        $students2 = DB::table('studentgrant_students')
            ->select('*')
            ->where('project_id', '=', $p_id)
            ->get();


        $typeMappings = [
            'IP' => 'Intellectual Property',
            'ProvisionalPatents' => 'Filed Provisional Patents',
            'GrantedPatents' => 'Granted Patents',
            'OpenSW' => 'Open Source Software',
            'SUp' => 'Start Up',
            'UG' => 'Under Graduate Student',
            'masters' => 'Masters Student',
            'PhD' => 'PhD Student',
            'q1' => 'Quartile-1 journal article'

            // Add more mappings as needed
        ];
        $commitments = DB::table('commitments')
            ->select('*')
            ->where('project_id', '=', $p_id)
            ->first();
        $divB = 0;
        $divA = 0;
        $divC = 0;
        if ($commitments) {
            $divB = $commitments->q1article * 8 + $commitments->q2article * 6 + $commitments->q3article * 4 + $commitments->q4article * 3 + $commitments->confArticle * 2 + $commitments->books * 8 + $commitments->editBooks * 6 + $commitments->chapters * 4;
            $divA = $commitments->ip * 4 + $commitments->filedPatent * 7 + $commitments->openSourceSW * 8 + $commitments->startUp * 10;
            $divC = $commitments->UG * 1 + $commitments->master * 2 + $commitments->Phd * 3;
        }
        if ($divA === 0)
            $divA = 1;
        if ($divB === 0)
            $divB = 1;
        if ($divC === 0)
            $divC = 1;



        if (is_object($project)) {
            $cycleTitle = $project->cycle_title ?? 'default_cycle';
            $oldId = $project->old_project_id ?? 'default_id';
        } elseif (is_array($project)) {
            $cycleTitle = $project['cycle_title'] ?? 'default_cycle';
            $oldId = $project['old_project_id'] ?? 'default_id';
        } else {
            $cycleTitle = 'default_cycle';
            $oldId = 'default_id';
        }

        $directory = "uploads/ethical_approvals/{$cycleTitle}/{$oldId}/";


        $files = Storage::files($directory);
        $fileNames = [];
        foreach ($files as $file) {
            $fileNames[] = basename($file);
        }

        //dd($commitments,$divA,$divB,$divC);
        $contributions = json_decode($contributions, true);
        $students = json_decode($students, true);
        //dd($contributions);
        if ($project->grant_type == 'regular')
            return view('gradingTabs', ['readiness' => $readiness, 'readiness_report' => $readiness_report, 'progressComments' => $progressComments, 'progressComments2' => $progressComments2, 'finalGrades' => $finalGrades, 'proposal_report' => $proposal_report, 'progress_report' => $progress_report, 'progress_report2' => $progress_report2, 'final_report' => $final_report, 'file' => '/serveFile', 'project' => $project, 'p_id' => $p_id, 'permit' => $permit, 'commitments' => $commitments, 'progressDraft' => $progressDraft, 'finalDraft' => $finalDraft, 'outcomes' => $outcomes, 'verify_outcomes' => $verify_outcomes, 'contributions' => $contributions, 'students' => $students, 'typeMappings' => $typeMappings, 'divA' => $divA, 'divB' => $divB, 'divC' => $divC]);
        else
            return view('studentsGrants/gradingTabs', ['next' => $next, 'fileNames' => $fileNames, 'readiness' => $readiness, 'readiness_report' => $readiness_report, 'progressComments' => $progressComments, 'progressComments2' => $progressComments2, 'finalGrades' => $finalGrades, 'proposal_report' => $proposal_report, 'progress_report' => $progress_report, 'progress_report2' => $progress_report2, 'final_report' => $final_report, 'file' => '/serveFile', 'project' => $project, 'p_id' => $p_id, 'permit' => $permit, 'commitments' => $commitments, 'progressDraft' => $progressDraft, 'finalDraft' => $finalDraft, 'outcomes' => $outcomes2, 'verify_outcomes' => $verify_outcomes, 'contributions' => $contributions, 'students' => $students2, 'typeMappings' => $typeMappings, 'divA' => $divA, 'divB' => $divB, 'divC' => $divC]);
    }

    public function serveFile3()
    {
        $half = request()->get('file');
        $path = '../storage/app/downloads/' . $half;
        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }


    public function serveFile2()
    {
        $half = request()->get('file');
        $path = '../storage/app/uploads/' . $half;
        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

    public function serveFile()
    {
        $half = request()->get('file');
        $path = '../storage/app/' . $half;
        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }
    public function reportUpload(Request $request, $p_id)
    {


        $recordExists = Project::where('id', $p_id)->where('user_id', Auth::user()->id)->first();

        if (!$recordExists) {
            return "Un-authorized";
        }

        $rules = [
            'file' => 'required|file|mimes:pdf',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput(['file1' => $request->report_type])->with(['mmtype' => $request->report_type]);
        }



        DB::transaction(function () use ($request, $p_id) {
            $project = DB::table('projects')
                ->selectRaw('projects.*,cycle.cycle_title')
                ->join('cycle', 'cycle.id', '=', 'projects.cycle')
                ->where('projects.id', '=', $p_id)->first();

            $logged_in_user_id = auth()->user()->id;
            if ($request->report_type === 'progress') {
                $path = Storage::putFileAs('uploads/progress_reports/' . $project->cycle_title . '/', $request->file('file'), $project->old_project_id . ".pdf");
                $type = Submissions::TYPE_PROGRESS;
            } else if ($request->report_type === 'progress2') {
                $path = Storage::putFileAs('uploads/progress_reports/' . $project->cycle_title . '/', $request->file('file'), $project->old_project_id . "_2.pdf");
                $type = Submissions::TYPE_PROGRESS2;
            } else if ($request->report_type === 'final') {
                $path = Storage::putFileAs('uploads/final_reports/' . $project->cycle_title . '/', $request->file('file'), $project->old_project_id . ".pdf");
                $type = Submissions::TYPE_FINAL;
            } else if ($request->report_type === 'readiness') {
                $path = Storage::putFileAs('uploads/readiness_reports/' . $project->cycle_title . '/', $request->file('file'), $project->old_project_id . ".pdf");
                $type = Submissions::TYPE_READINESS;
            }

            // Upsert record into submissions table
            DB::table('submissions')->updateOrInsert(
                ['project_id' => $p_id, 'type' => $type], // Criteria for matching
                [
                    'title' => $request->title,
                    'user_id' => $logged_in_user_id,
                    'due_date' => '2023-12-12 00:00:00',
                    'updated_at' => now(), // Make sure to update the timestamp
                ]
            );
        });

        if ($request->report_type == 'final')
            return redirect()->back()->with('successreportfinal', '<p style="color: green;">Final report uploaded successfully</p>');
        else if ($request->report_type == 'progress')
            return redirect()->back()->with('successreport', '<p style="color: green;">Progress report uploaded successfully</p>');
        else if ($request->report_type == 'progress2')
            return redirect()->back()->with('successreport2', '<p style="color: green;">Progress report 2 uploaded successfully</p>');
        else if ($request->report_type == 'readiness')
            return redirect()->back()->with('successreadiness', '<p style="color: green;">QU readiness mapping uploaded successfully</p>');
        else if ($request->report_type == 'proposal')
            return redirect()->back()->with('successproposal', '<p style="color: green;">Proposal uploaded successfully</p>');
    }

    public function assignViewCycle()
    {
        $type = auth()->user()->type;
        return view('assignReviewerCycle', ['type' => $type]);
    }

    public function assignView($cycle)
    {
        $reviewerUsers = User::join('user_pillars', 'users.id', '=', 'user_pillars.user_id')
            ->where('users.type', 'Reviewer')
            ->orWhere('users.type', 'LPI+Reviewer')
            ->select('users.*', 'user_pillars.pillar_id')
            ->get();
        //    dd($reviewerUsers);
        $data = DB::table('projects')
            ->whereNotIn('id', DB::table('projects_reviewers')->pluck('project_id'))
            ->get();
        //   dd($data);
        $data = json_decode($data, true);
        $reviewer = DB::table('users')
            ->select('name', 'email', 'id')
            ->where('type', '=', 'Reviewer')
            ->orWhere('type', '=', 'LPI+Reviewer')
            ->get();
        $reviewerByPillar = $reviewerUsers->groupBy('pillar_id');
        // dd($reviewerByPillar);
        return view('assignReviewer', ['projects' => $data, 'cycle' => $cycle, 'reviewerByPillar' => $reviewerByPillar]);
    }
    public function gradedProjects()
    {
        $type = auth()->user()->type;
        $user = auth()->user()->id;
        if ($type === User::TYPE_ADMIN) {

            $data = Project::leftJoin('final_report_grading', 'final_report_grading.project_id', '=', 'projects.id')
                ->select('projects.*', 'final_report_grading.total')

                ->get();


            return view('gradedProjects', ['data' => $data, 'type' => $type]);
        } else {

            $data = Project::leftJoin('final_report_grading', 'final_report_grading.project_id', '=', 'projects.id')
                ->select('projects.*', 'final_report_grading.total')
                ->where('projects.user_id', '=', $user)
                ->get();
            return view('gradedProjects', ['data' => $data, 'type' => $type]);
        }
    }
    public function outcome(Request $request)
    {

        Outcome::where('project_id', $request->p_id)
            ->update([
                'user_id' => auth()->user()->id,
                'outcomes' => $request->outcome
            ]);
        return back()->with('success', 'Item created successfully!');
        return view('home');
    }
    public function graded()
    {
        return view('gradedProjects');
    }
    public function ajaxListGradedprojects()
    {

        $type = auth()->user()->type;
        $user = auth()->user()->id;
        if ($type === User::TYPE_ADMIN) {
            $data = DB::table('projects as p')
                ->select(
                    'p.id',
                    'p.old_project_id',
                    'p.title',
                    DB::raw('SUM(CASE WHEN fin.publish = "accepted" THEN fin.total ELSE 0 END) AS total'),
                    DB::raw('AVG(CASE WHEN fin.publish = "accepted" THEN fin.total ELSE 0 END) AS avg'),
                    DB::raw('CASE
                            WHEN (MAX(CASE WHEN prg.rn = 1 THEN prg.isAccepted END)) = 0 THEN "Rejected"
                            WHEN (MAX(CASE WHEN prg.rn = 1 THEN prg.isAccepted END)) = 1 THEN "Accepted"
                            ELSE "Not Graded"
                        END AS prg_Reviewer1'),
                    DB::raw('CASE
                            WHEN (MAX(CASE WHEN prg.rn = 2 THEN prg.isAccepted END)) = 0 THEN "Rejected"
                            WHEN (MAX(CASE WHEN prg.rn = 2 THEN prg.isAccepted END)) = 1 THEN "Accepted"
                            ELSE "Not Graded"
                        END AS prg_Reviewer2'),
                    DB::raw('CASE
                            WHEN (MAX(CASE WHEN fin.rn = 1 THEN fin.isAccepted END)) = 0 THEN "Graded"
                            WHEN (MAX(CASE WHEN fin.rn = 1 THEN fin.isAccepted END)) = 1 THEN "Graded"
                            ELSE "Not Graded"
                        END AS fin_Reviewer1'),
                    DB::raw('CASE
                            WHEN (MAX(CASE WHEN fin.rn = 2 THEN fin.isAccepted END)) = 0 THEN "Graded"
                            WHEN (MAX(CASE WHEN fin.rn = 2 THEN fin.isAccepted END)) = 1 THEN "Graded"
                            ELSE "Not Graded"
                        END AS fin_Reviewer2')
                )
                ->leftJoin(DB::raw('(SELECT project_id, isAccepted, publish, ROW_NUMBER() OVER (PARTITION BY project_id ORDER BY id) AS rn FROM progress_report_grading WHERE publish = "accepted") as prg'), 'p.id', '=', 'prg.project_id')
                ->leftJoin(DB::raw('(SELECT project_id, isAccepted, publish, total, ROW_NUMBER() OVER (PARTITION BY project_id ORDER BY id) AS rn FROM final_report_grading WHERE publish = "accepted") as fin'), 'p.id', '=', 'fin.project_id')

                ->where(function ($query) {
                    $query->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('progress_report_grading')
                            ->whereColumn('progress_report_grading.project_id', 'p.id')
                            ->where('progress_report_grading.publish', '=', 'accepted');
                    })
                        ->orWhereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('final_report_grading')
                                ->whereColumn('final_report_grading.project_id', 'p.id')
                                ->where('final_report_grading.publish', '=', 'accepted');
                        });
                })
                ->groupBy('p.id', 'p.old_project_id', 'p.title')
                ->get();

            // $data = DB::table('projects as p')
            //     ->select(
            //         'p.id as id',
            //         'p.old_project_id',
            //         'p.title',
            //         DB::raw('SUM(fg.total) as total'),
            //         DB::raw('AVG(fg.total) as avg'),
            //         DB::raw("CASE WHEN (MAX(CASE WHEN prg.rn = 1 THEN prg.isAccepted END)) = 0 THEN 'Rejected' ELSE 'Accepted' END AS `Reviewer1`"),
            //         DB::raw("CASE WHEN (MAX(CASE WHEN prg.rn = 2 THEN prg.isAccepted END)) = 0 THEN 'Rejected' ELSE 'Accepted' END AS `Reviewer2`")
            //     )
            //     ->join(DB::raw('(SELECT project_id, isAccepted,publish, ROW_NUMBER() OVER (PARTITION BY project_id ORDER BY id) AS rn FROM progress_report_grading) as prg'), 'p.id', '=', 'prg.project_id')
            //     ->join('final_report_grading as fg', 'fg.project_id', '=', 'p.id')
            //     ->where('fg.publish', '=', 'accepted')
            //     ->where('prg.publish', '=', 'accepted')
            //     ->groupBy('p.id', 'p.old_project_id', 'p.title')
            //     ->get();
        } else {
            // $data = DB::table('projects as p')
            //     ->select(
            //         'p.id as id',
            //         'p.old_project_id',
            //         'p.title',
            //         DB::raw('SUM(fg.total) as total'),
            //         DB::raw('AVG(fg.total) as avg'),
            //         DB::raw("CASE WHEN (MAX(CASE WHEN prg.rn = 1 THEN prg.isAccepted END)) = 0 THEN 'Rejected' ELSE 'Accepted' END AS `Reviewer1`"),
            //         DB::raw("CASE WHEN (MAX(CASE WHEN prg.rn = 2 THEN prg.isAccepted END)) = 0 THEN 'Rejected' ELSE 'Accepted' END AS `Reviewer2`")
            //     )
            //     ->join(DB::raw('(SELECT project_id, isAccepted,publish, ROW_NUMBER() OVER (PARTITION BY project_id ORDER BY id) AS rn FROM progress_report_grading) as prg'), 'p.id', '=', 'prg.project_id')
            //     ->join('final_report_grading as fg', 'fg.project_id', '=', 'p.id')
            //     ->groupBy('p.id', 'p.old_project_id', 'p.title')
            //     ->where('projects.user_id', '=', $user)
            //     ->where('fg.publish', '=', 'accepted')
            //     ->get();

            $data = DB::table('projects as p')
                ->select(
                    'p.id',
                    'p.old_project_id',
                    'p.title',
                    DB::raw('SUM(CASE WHEN fin.publish = "accepted" THEN fin.total ELSE 0 END) AS total'),
                    DB::raw('AVG(CASE WHEN fin.publish = "accepted" THEN fin.total ELSE 0 END) AS avg'),
                    DB::raw('CASE
                            WHEN (MAX(CASE WHEN prg.rn = 1 THEN prg.isAccepted END)) = 0 THEN "Rejected"
                            WHEN (MAX(CASE WHEN prg.rn = 1 THEN prg.isAccepted END)) = 1 THEN "Accepted"
                            ELSE "Not Graded"
                        END AS prg_Reviewer1'),
                    DB::raw('CASE
                            WHEN (MAX(CASE WHEN prg.rn = 2 THEN prg.isAccepted END)) = 0 THEN "Rejected"
                            WHEN (MAX(CASE WHEN prg.rn = 2 THEN prg.isAccepted END)) = 1 THEN "Accepted"
                            ELSE "Not Graded"
                        END AS prg_Reviewer2'),
                    DB::raw('CASE
                            WHEN (MAX(CASE WHEN fin.rn = 1 THEN fin.isAccepted END)) = 0 THEN "Graded"
                            WHEN (MAX(CASE WHEN fin.rn = 1 THEN fin.isAccepted END)) = 1 THEN "Graded"
                            ELSE "NA"
                        END AS fin_Reviewer1'),
                    DB::raw('CASE
                            WHEN (MAX(CASE WHEN fin.rn = 2 THEN fin.isAccepted END)) = 0 THEN "Graded"
                            WHEN (MAX(CASE WHEN fin.rn = 2 THEN fin.isAccepted END)) = 1 THEN "Graded"
                            ELSE "NA"
                        END AS fin_Reviewer2')
                )
                ->leftJoin(DB::raw('(SELECT project_id, isAccepted, publish, ROW_NUMBER() OVER (PARTITION BY project_id ORDER BY id) AS rn FROM progress_report_grading WHERE publish = "accepted") as prg'), 'p.id', '=', 'prg.project_id')
                ->leftJoin(DB::raw('(SELECT project_id, isAccepted, publish, total, ROW_NUMBER() OVER (PARTITION BY project_id ORDER BY id) AS rn FROM final_report_grading WHERE publish = "accepted") as fin'), 'p.id', '=', 'fin.project_id')
                ->where('p.user_id', '=', $user)

                ->where(function ($query) {
                    $query->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('progress_report_grading')
                            ->whereColumn('progress_report_grading.project_id', 'p.id')
                            ->where('progress_report_grading.publish', '=', 'accepted');
                    })
                        ->orWhereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('final_report_grading')
                                ->whereColumn('final_report_grading.project_id', 'p.id')
                                ->where('final_report_grading.publish', '=', 'accepted');
                        });
                })
                ->groupBy('p.id', 'p.old_project_id', 'p.title')
                ->get();
        }


        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('gradingDetails', ['p_id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Report Card</a>';

                return   $Btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function commitments()
    {
        return view('projectOutcomes');
    }

    public function createProject(REQUEST $request)
    {
        $project = $request->all();
        //     dd($request);


        $request->session()->put('project', $project);
        $record = FromConfTool::find($project['conf_tool_id']);
        $arrPillar = explode(',', $record['pillars']);
        $arrTags = explode(',', $record['tags']);

        $pillars =  DB::table('pillars')
            ->select('pillar', 'subpillar')
            ->whereIn('subpillar', $arrPillar)
            ->get();
        $tags =  DB::table('tags')
            ->select('tagtitle')
            ->whereIn('tag', $arrTags)
            ->distinct()
            ->get();


        $request->session()->put('pillars', $pillars);
        $request->session()->put('tags', $tags);

        // $pillars2 = Pillars::all()->pluck('pillar','subpillar', 'id')->toArray();
        $tags2 = Tags::all()->pluck('tag', 'id')->toArray();
        $request->session()->put('tags2', $tags2);


        $pillarsCollection = Pillars::all(['pillar', 'subpillar', 'id']);
        $pillars2 = [];
        foreach ($pillarsCollection as $pillar) {
            $pillars2[$pillar->pillar][] = $pillar->subpillar;
        }
        $uniquePillars = array_keys($pillars2);
        $request->session()->put('pillars2', $pillars2);
        $request->session()->put('uniquePillars', $uniquePillars);
        return redirect('projectStep2');
    }

    public function createProjectStep2(REQUEST $request)
    {
        $project_meta = $request->all();
        $request->session()->put('project_meta', $project_meta);
        $cycle = DB::table('cycle')
            ->select('*')
            ->where('id', session('project')['cycle'])
            ->first()->cycle_title;
        $request->session()->put('cycle', $cycle);

        if (session('project')['grant_type'] == 'regular') {
            return redirect('projectStep3');
        } else {
            $requestData = [];
            $request = new Request($requestData);
            $this->createProjectStep3($request);
            return redirect('home')->with('successproject', '<p style="color: green;"> Project added successfully</p>');
        }
    }
    public function updateCommitments(REQUEST $request)
    {
        Commitments::updateOrCreate(
            ['project_id' => $request->project_id],
            [
                "q1article" => $request->q1article,
                "q2article" => $request->q2article,
                "q3article" => $request->q3article,
                "q4article" => $request->q4article,
                "confArticle" => $request->confArticle,
                "books" => $request->books,
                "editBooks" => $request->editBooks,
                "chapters" => $request->chapters,
                "ip" => $request->ip,
                "filedPatent" => $request->filedPatent,
                "openSourceSW" => $request->openSourceSW,
                "startUp" => $request->startUp,
                "ethical" => $request->ethical == 'Yes' ? 1 : 0,
                "master" => $request->master,
                "UG" => $request->UG,
                "Phd" => $request->Phd,
                "crossCollege" => $request->crossCollege
            ]
        );
    }
    public function createProjectStep3(REQUEST $request)
    {


        if (session('project')['grant_type'] == 'regular') {


            $request->validate([
                'Q4' => 'numeric',
                'Q3' => 'numeric',
                'Q2' => 'numeric',
                'Q1' => 'numeric',
                'conf' => 'numeric',
                'book' => 'numeric',
                'book_edit' => 'numeric',
                'chap' => 'numeric',
                'form' => 'numeric',
                'patent' => 'numeric',
                'sw' => 'required',
                'start_up' => 'required',
                'ethical' => 'required',
                'UG' => 'required|numeric',
                'masters' => 'required|numeric',
                'phd' => 'required|numeric',
                'crossClg' => 'required'
            ]);
        }


        DB::transaction(function () use ($request) {

            $project_meta = session()->get('project_meta');
            $project_data = session()->get('project');


            if (auth()->user()->type === User::TYPE_ADMIN)
                $isAdmin = 1;
            else
                $isAdmin = 0;

            $project = Project::create([
                "title" => $project_data['title'],
                "old_project_id" => $project_data['old_project_id'],
                "conf_tool_id" => $project_data['conf_tool_id'],
                "status" => Project::STATUS_ACCEPTED,
                "user_id" =>   $project_data['users'],
                "cycle" => $project_data['cycle'],
                "isAdmin" => $isAdmin
            ]);


            Projects_stakeholder::create([
                "project_id" => $project->id,
                "user_id" => $project_data['users'],
            ]);

            if (session('project')['grant_type'] == 'regular') {

                $project_commit = $request->all();
                $request->session()->put('project_commit', $project_commit);
                $project_commit = session()->get('project_commit');

                Commitments::create([
                    "project_id" => $project->id,
                    "q1article" => $project_commit['Q1'],
                    "q2article" => $project_commit['Q2'],
                    "q3article" => $project_commit['Q3'],
                    "q4article" => $project_commit['Q4'],
                    "confArticle" => $project_commit['conf'],
                    "books" => $project_commit['book_publish'],
                    "editBooks" => $project_commit['book_edit'],
                    "chapters" => $project_commit['chap'],
                    "ip" => $project_commit['form'],
                    "filedPatent" => $project_commit['patent'],
                    "openSourceSW" => $project_commit['sw'],
                    "startUp" => $project_commit['start_up'],
                    "ethical" => $project_commit['ethical'],
                    "master" => $project_commit['masters'],
                    "UG" => $project_commit['UG'],
                    "Phd" => $project_commit['phd'],
                    "crossCollege" => $project_commit['crossClg'],
                ]);
            }
            $record = FromConfTool::find($project_data['conf_tool_id']);
            $arrPillar = explode(',', $record['pillars']);

            if (count($arrPillar) > 0) {
                foreach ($arrPillar as $item) {
                    $pillar = Pillars::where('subpillar', '=', $item)->first();
                    if ($pillar)
                        ProjectPillar::create([
                            "project_id" => $project->id,
                            "pillar_id" => $pillar->id,
                        ]);
                }
            } else {
                $project_meta = session()->get('project_meta');
                $pillar = Pillars::where('subpillar', '=', $project_meta['subpillar'])->first();
                ProjectPillar::create([
                    "project_id" => $project->id,
                    "pillar_id" => $pillar->id,
                ]);
            }

            $arrTags = explode(',', $record['tags']);


            if (count($arrTags) > 0) {
                foreach ($arrTags as $item) {
                    $tag = Tags::where('tag', '=', $item)->first();
                    if ($tag)
                        ProjectTag::create([
                            "project_id" => $project->id,
                            "tag_id" => $tag->id,
                        ]);
                }
            } else {
                $project_meta = session()->get('project_meta');

                $tag = Tags::where('tag', '=', $project_meta['tags'])->first();

                ProjectTag::create([
                    "project_id" => $project->id,
                    "tag_id" => $tag->id,
                ]);
            }

            if ($record) {
                $record->added = 1;
                $record->save();
            }
        });

        //   $record = FromConfTool::find(session()->get('project')['conf_tool_id']);
        //      $ctrl = new EmailController();
        //      $ctrl->project_added(session()->get('project')['users']);

        //   return redirect('home')->with('successproject', '<p style="color: green;"> Project added successfully</p>');
        // return redirect()->back()->with([
        //     'success123' => true, // This flag will trigger the modal
        //     'success_message' => 'Project added successfully',
        //     'project_meta' => session('project_meta'),
        //     'cycle' => session('cycle')
        // ]);

        return redirect()->route('confprojects', ['c_id' => session()->get('project')['cycle']])->with([
            'success123' => true, // This flag will trigger the modal
            'success_message' => 'Project registered successfully',
            'project_meta' => session('project_meta'),
            'cycle' => session('cycle')
        ]);
        // return redirect()->route('confprojects')->with([
        //     'success123' => true, // This flag will trigger the modal
        //     'success_message' => 'Project registered successfully',
        //     'project_meta' => session('project_meta'),
        //     'cycle' => session('cycle')
        // ]);
    }

    public function bulk(Request $request)
    {
        $ctrl = new EmailController();

        foreach ($request->all() as $key => $value) {
            if ($key !== '_token') {
                if ($key !== 'usertable_length') {
                    $p_id = (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT);
                    $r_id =  Projects_reviewer::create([
                        "project_id" => $p_id,
                        "user_id" => $request[$key],
                    ]);

                    $ctrl->reviewerAssigned($r_id);
                }
            }
        }
        return redirect()->back()->with('successbulkreviewer', '<p style="color: green;">Reviewers assigned successfully</p>');
    }

    public function countReviewer()
    {
        $projects = DB::table('projects')
            ->whereNotIn('id', DB::table('projects_reviewers')->pluck('project_id'))
            ->get();
        // dd($projects);
    }

    public function AssignedReviewers()
    {
        $projects = Projects_reviewer::leftJoin('projects', 'projects_reviewers.project_id', '=', 'projects.id')
            ->join('users', 'users.id', '=', 'projects_reviewers.user_id')
            ->selectRaw('projects_reviewers.project_id,max(projects.title) as title,group_concat(users.name) as reviewers')
            ->groupBy('projects_reviewers.project_id')
            ->get();
        $projects = json_decode($projects, true);
        return view('AssignedReviewers');
    }
    public function reviewer()
    {
        $reviewers = DB::table('users')
            ->select('name', 'id', 'email')
            ->where('type', '=', 'Reviewer')
            ->orwhere('type', '=', 'LPI+Reviewer')
            ->get();
        $reviewers = json_decode($reviewers, true);
        //dd($reviewers);
        return view('reviewer', ['list' => $reviewers]);
    }

    public function printed(Request $request)
    {
        dd($request);
    }
}
