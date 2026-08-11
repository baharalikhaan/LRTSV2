<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use Illuminate\Http\Request;
use App\Models\ProjectTag;
use App\Models\ProjectPillar;
use App\Models\Projects_reviewer;
use App\Models\Submissions;
use App\Models\FinalReportGrading;
use App\Models\ProgressReportGrading;
use App\Models\User;
use App\Models\Project;
use App\Models\Outcome;
use App\Models\Projects_stakeholder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use DataTables;
use Illuminate\Support\Facades\Http;

class DetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function projectDetails($p_id)
    {
        $commitments = DB::table('commitments')
            ->select('*')
            ->where('project_id', '=', $p_id)
            ->first();
        $tags = ProjectTag::leftJoin('tags', 'project_tag.tag_id', '=', 'tags.id')
            ->select('project_tag.*', 'tags.*')
            ->where('project_tag.project_id', '=', $p_id)
            ->get();
        $pillars = ProjectPillar::leftJoin('pillars', 'project_pillar.pillar_id', '=', 'pillars.id')
            ->select('pillars.pillar', 'pillars.subpillar')
            ->where('project_pillar.project_id', '=', $p_id)
            ->get();

        $finalReport = Project::leftJoin('final_report_grading', 'final_report_grading.project_id', '=', 'projects.id')
            ->select('final_report_grading.*')
            ->where('projects.id', '=', $p_id)
            ->first();



        $stakeholder = Projects_stakeholder::leftJoin('users', 'projects_stakeholders.user_id', '=', 'users.id')
            ->select('projects_stakeholders.*', 'users.*')
            ->where('projects_stakeholders.project_id', '=', $p_id)
            ->get();
        $reviewers = Projects_reviewer::leftJoin('users', 'projects_reviewers.user_id', '=', 'users.id')
            ->select('projects_reviewers.*', 'users.*')
            ->where('projects_reviewers.project_id', '=', $p_id)
            ->get();
        $project = DB::table('projects')
            ->select('id', 'title', 'user_id', 'status', 'old_project_id')
            ->where('id', '=', $p_id)
            ->first();
        $reviewer_list = DB::table('users')
            ->select('*')
            ->where('type', '=', 'Reviewer')
            ->orWhere('type', '=', 'LPI+Reviewer')
            ->get();
        $tags = json_decode($tags, true);

        $reviewers = json_decode($reviewers, true);
        $stakeholder = json_decode($stakeholder, true);


        //get list from API
        $jsonString = null;

        try {
            $response = Http::get('https://residence.qu.edu.qa/ords/qucust/quapi/getProjects/123$$321');
            if ($response->successful()) {
                $jsonString  = $response;
            }
        } catch (\Exception $e) {
        }

        $data = json_decode($jsonString, true);
        $project_num = "";
        if ($data) {

            foreach ($data['items'] as $value) {
                if ($value['project_name'] == $project->old_project_id)
                    $project_num = $value['project_num'];
            }
        } {
        }




        //budget api call
        //https://residence.qu.edu.qa/ords/qucust/quapi/getProjectBudget/1007135/123$$321
        // $project_api = DB::table('project_api')
        //     ->select('*')
        //     ->where('project_name', '=', $project->old_project_id)
        //     ->first();
        // dd($project_api->project_num);
        // $projectid = '1007135';


        $budgetdata = '';
        try {
            $response = Http::get('https://residence.qu.edu.qa/ords/qucust/quapi/getProjectBudget/' . $project_num . '/123$$321');
            if ($response->successful()) {
                $budgetdata  = $response;
            }
        } catch (\Exception $e) {
        }

        $error = '';
        if ($project_num == '')
            $error = 'No response from API';
        else if ($budgetdata == '')
            $error = 'Project data not found';

        //    dd($budgetdata);

        return view('projectDetails', ['finalReport' => $finalReport, 'error' => $error, 'budgetdata' => json_decode($budgetdata), 'stakeholder' => $stakeholder, 'tags' => $tags, 'pillars' => $pillars, 'reviewers' => $reviewers, 'project' => $project, 'list' => $reviewer_list, 'commitments' => $commitments]);
    }


    public function updateProjectTag(Request $request, $p_id)
    {

        $request->validate([
            'tag' => 'required',

        ]);

        DB::transaction(function () use ($request, $p_id) {
            ProjectTag::where('project_id', $p_id)->delete();
            foreach ($request->tag as $tag) {
                ProjectTag::create([
                    "project_id" => $p_id,
                    "tag_id" => $tag,
                ]);
            }
        });
        view('home', ['message' => 'Tags updated Successfully']);
    }

    public function updateProjectPillar(Request $request, $p_id)
    {
        $request->validate([
            'pillar' => 'required',

        ]);

        DB::transaction(function () use ($request, $p_id) {
            ProjectPillar::where('project_id', $p_id)->delete();
            foreach ($request->pillar as $pillar) {
                ProjectPillar::create([
                    "project_id" => $p_id,
                    "pillar_id" => $pillar,
                ]);
            }
        });
    }

    public function assignReviewer(Request $request, $p_id)
    {
        $request->validate([
            'reviewerA' => 'required',
            'reviewerB' => 'required',

        ]);
        DB::transaction(function () use ($request, $p_id) {
            Projects_reviewer::where('project_id', $p_id)->delete();

            Projects_reviewer::create([
                "project_id" => $p_id,
                "user_id" => $request->reviewerA,
            ]);
            Projects_reviewer::create([
                "project_id" => $p_id,
                "user_id" => $request->reviewerB,
            ]);
        });
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
    public function gradingDetailsPublic($hash)
    {
        $p_id = DB::table('projects')
            ->select('id')
            ->where(DB::raw('SUBSTRING(SHA2(old_project_id, 256), 1, 10)'), '=', $hash)
            ->first()->id;


                    // dd($p_id);
        $progressGrades = ProgressReportGrading::leftJoin('users', 'progress_report_grading.user_id', '=', 'users.id')
            ->join('ratings as rt1', 'progress_report_grading.achievementsRating', '=', 'rt1.id')
            ->join('ratings as rt2', 'progress_report_grading.publicationsRating', '=', 'rt2.id')
            ->join('ratings as rt3', 'progress_report_grading.studentsRating', '=', 'rt3.id')
            ->join('ratings as rt4', 'progress_report_grading.budgetRating', '=', 'rt4.id')
            ->selectRaw('progress_report_grading.*, users.*,rt1.rating as rt1, rt2.rating as rt2, rt3.rating as rt3, rt4.rating as rt4')
            ->where('progress_report_grading.project_id', '=', $p_id)
            ->where('progress_report_grading.publish', '=', 'accepted')
            ->get();

        // dd($p_id);
        // $progressGrades = ProgressReportGrading::leftJoin('users', 'progress_report_grading.user_id', '=', 'users.id')
        //     ->select('progress_report_grading.*', 'users.*')
        //     ->where('progress_report_grading.project_id', '=', $p_id)
        //     ->where('progress_report_grading.publish', '=', 'accepted')
        //     ->get();


        $finalGrades = FinalReportGrading::leftJoin('users', 'final_report_grading.user_id', '=', 'users.id')
            ->select('final_report_grading.*', 'users.*')
            ->where('final_report_grading.project_id', '=', $p_id)
            ->where('final_report_grading.publish', '=', 'accepted')
            ->get();
        $sum = DB::table('final_report_grading')
            ->selectRaw('sum(final_report_grading.total) as sum')
            ->where('project_id', '=', $p_id)
            ->where('final_report_grading.publish', '=', 'accepted')
            ->first();
        $avg = DB::table('final_report_grading')
            ->selectRaw('avg(final_report_grading.total) as avg')
            ->where('project_id', '=', $p_id)
            ->where('final_report_grading.publish', '=', 'accepted')
            ->first();
        // $sum=json_decode($sum,true);
        $project = DB::table('projects')->select('*')
            ->where('id', '=', $p_id)
            ->selectSub(function ($query) {
                $query->from('final_report_grading')
                    ->whereRaw('final_report_grading.project_id = projects.id')
                    ->selectRaw('sum(final_report_grading.total)');
            }, 'sum')
            ->selectSub(function ($query) {
                $query->from('final_report_grading')
                    ->whereRaw('final_report_grading.project_id = projects.id')
                    ->selectRaw('avg(final_report_grading.total)');
            }, 'avg')
            ->where('projects.status', '=', 'Accepted')
            ->orWhere('projects.status', '=', 'Completed')
            ->first();
        $progressGrades = json_decode($progressGrades, true);
        $finalGrades = json_decode($finalGrades, true);
      //      dd($progressGrades);
        // dd($project->avg, $project->sum, $project->id,$finalGrades,$sum->sum);
        return view('gradingDetails', ['finalGrades' => $finalGrades, 'progressGrades' => $progressGrades, 'project' => $project, 'value' => '1', 'p_id' => $p_id, 'sum' => $sum, 'avg' => $avg]);
    }


    /**
     * The function `reviewerEvaluationPublic` retrieves reviewer evaluation data based on a hashed user ID
     * and displays it in a view.
     *
     * @param hash The `reviewerEvaluationPublic` function seems to retrieve information about a user and
     * their reviewer grading data based on a given hash value. The hash value is used to find the user's
     * ID in the `users` table.
     *
     * @return This function returns a view called 'reviewerEvaluation' with two variables passed to it -
     * 'user' and 'data'. The 'user' variable contains information about a specific user fetched from the
     * 'users' table based on the provided hash value. The 'data' variable contains information retrieved
     * from the 'reviewer_grading' table joined with the 'cycle' table, filtered by the user ID
     */
    public function reviewerEvaluationPublic($hash)
    {
        //u_id the target user by hash
        $u_id = DB::table('users')
            ->select('id')
            ->where(DB::raw(' SUBSTRING(SHA2(id, 256),1,10)'), '=', $hash)
            ->first()->id;

        $user = DB::table('users')
            ->select('*')
            ->where('users.id', '=', $u_id)
            ->first();

        $data = DB::table('reviewer_grading')
            ->join('cycle', 'cycle.id', '=', 'reviewer_grading.cycle')
            ->select(DB::raw('cycle.cycle_title,conflict,responsiveness,comprehensiveness,no_reviewers,behaviour,
        scope_of_supply, mode_of_selection, basis_of_approval, type_extent_of_control, designation_of_approver'))
            ->where('reviewer', '=', $u_id)
            ->get();

        return view('reviewerEvaluation', ['user' => $user, 'data' => $data]);
    }
    public function reviewerEvaluation($u_id)
    {
        $user = DB::table('users')
            ->select('*')
            ->where('users.id', '=', $u_id)
            ->first();

        $data = DB::table('reviewer_grading')
            ->join('cycle', 'cycle.id', '=', 'reviewer_grading.cycle')
            ->select(DB::raw('cycle.cycle_title,conflict,responsiveness,comprehensiveness,no_reviewers,behaviour,
        scope_of_supply, mode_of_selection, basis_of_approval, type_extent_of_control, designation_of_approver'))
            ->where('reviewer', '=', $u_id)
            ->get();

        return view('reviewerEvaluation', ['user' => $user, 'data' => $data]);
    }

    public function gradingDetailsLagacy($p_id)
    {

        $project = DB::table('gradingdetailslagacy')
            ->selectRaw('*')
            ->where('id', '=', $p_id)

            ->first();
        return view('gradingDetailsLagacy', ['project' => $project]);
    }
    public function gradingDetails($p_id)
    {
        // dd($p_id);
        $progressGrades = ProgressReportGrading::leftJoin('users', 'progress_report_grading.user_id', '=', 'users.id')
            ->join('ratings as rt1', 'progress_report_grading.achievementsRating', '=', 'rt1.id')
            ->join('ratings as rt2', 'progress_report_grading.publicationsRating', '=', 'rt2.id')
            ->join('ratings as rt3', 'progress_report_grading.studentsRating', '=', 'rt3.id')
            ->join('ratings as rt4', 'progress_report_grading.budgetRating', '=', 'rt4.id')
            ->selectRaw('progress_report_grading.*, users.*,rt1.rating as rt1, rt2.rating as rt2, rt3.rating as rt3, rt4.rating as rt4')
            ->where('progress_report_grading.project_id', '=', $p_id)
            ->where('progress_report_grading.publish', '=', 'accepted')
            ->get();

        $finalGrades = FinalReportGrading::leftJoin('users', 'final_report_grading.user_id', '=', 'users.id')
            ->select('final_report_grading.*', 'users.*')
            ->where('final_report_grading.project_id', '=', $p_id)
            ->where('final_report_grading.publish', '=', 'accepted')
            ->get();


        $sum = DB::table('final_report_grading')
            ->selectRaw('sum(final_report_grading.total) as sum')
            ->where('project_id', '=', $p_id)
            ->where('final_report_grading.publish', '=', 'accepted')
            ->first();
        $avg = DB::table('final_report_grading')
            ->selectRaw('avg(final_report_grading.total) as avg')
            ->where('project_id', '=', $p_id)
            ->where('final_report_grading.publish', '=', 'accepted')
            ->first();
        // $sum=json_decode($sum,true);
        $project = DB::table('projects')->select('*')
            ->where('id', '=', $p_id)
            ->selectSub(function ($query) {
                $query->from('final_report_grading')
                    ->whereRaw('final_report_grading.project_id = projects.id')
                    ->selectRaw('sum(final_report_grading.total)');
            }, 'sum')
            ->selectSub(function ($query) {
                $query->from('final_report_grading')
                    ->whereRaw('final_report_grading.project_id = projects.id')
                    ->selectRaw('avg(final_report_grading.total)');
            }, 'avg')
            ->where('projects.status', '=', 'Accepted')
            ->orWhere('projects.status', '=', 'Completed')
            ->first();
        $progressGrades = json_decode($progressGrades, true);
        $finalGrades = json_decode($finalGrades, true);
        //    dd($project);
        // dd($project->avg, $project->sum, $project->id,$finalGrades,$sum->sum);
        return view('gradingDetails', ['finalGrades' => $finalGrades, 'progressGrades' => $progressGrades, 'project' => $project, 'value' => '1', 'p_id' => $p_id, 'sum' => $sum, 'avg' => $avg]);
    }

    public function dashboard()
    {
        if (auth()->user()->type == User::TYPE_ADMIN) {
            return view('dashboard');
        } else
            return view('home', ['message' => "You are not authorized for this task"]);
    }
    public function projectOutcomes(Request $request)
    {

        $user = auth()->user()->name;

        $curl = curl_init();



        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://doi.org/10.13026/1n74-ne17",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
        //return view('home',);
    }

    public function userDetail($u_id)
    {
        $u_id = $u_id;
        $user = DB::table('users')->select('*')
            ->where('id', '=', $u_id)
            ->first();
        $projects = Project::leftJoin('grading_details', 'projects.id', '=', 'grading_details.project_id')
            ->select('projects.*', 'grading_details.reviewer_grade', 'grading_details.outcome_grade')
            ->where('projects.user_id', '=', $u_id)
            ->orderBy('projects.id', 'DESC')
            ->get();

        $sum = DB::table('projects', 'p')->join('cycle', 'p.cycle', '=', 'cycle.id')
            ->selectRaw('sum(p.total_score) as sum')
            ->groupBy(['p.user_id'])
            ->where('user_id', '=', $u_id)
            ->where('cycle.status', '=', 'active')
            ->pluck('p.sum');
        // $avg=json_decode($avg,true);
        //
        // dd((float)$avg);
        $sum = str_replace(array('[', ']', '"', '"'), '', $sum);
        //dd($avg);
        $cycle_score = DB::table('projects', 'p')
            ->selectRaw('p.cycle,avg(p.total_score) as avg')
            ->groupBy(['p.cycle'])
            ->where('user_id', '=', $u_id)
            ->orderBy('p.cycle', 'DESC')
            ->pluck('p.cycle', 'p.avg');
        $labels = $cycle_score->values();

        $data = $cycle_score->keys();
        // dd($labels,$data);
        $guage = DB::table('guage_settings')->find(1);

        $projects = Project::where('user_id', $u_id)->get();

        $projectIds = $projects->pluck('id')->toArray();

        // Fetching limited records from final_report_grading per project
        $limitedGradients = DB::table('final_report_grading')
            ->select('project_id', 'total')
            ->whereIn('project_id', $projectIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('project_id')
            ->map(function ($items) {
                return $items->take(2);
            });

        // Combine the project data and relevant gradients
        $combined = $projects->map(function ($project) use ($limitedGradients) {
            $project['gradients'] = $limitedGradients[$project['id']] ?? collect([]);
            return $project;
        });
        // dd($projects);

        //for gauge-1
        $sumOfAvgScores = DB::table('cycle')
            ->join('projects', 'cycle.id', '=', 'projects.cycle')
            ->select(DB::raw('SUM(projects.total_score) / COUNT(DISTINCT cycle.id) as sum_of_avg_scores'))
            ->where('user_id', '=', $u_id)
            ->where('cycle.status', '=', 'active')
            ->value('sum_of_avg_scores');

        //for gauge-2
        $sumOfoutcome = DB::table('projects')
            ->join('outcomes', 'projects.id', '=', 'outcomes.project_id')
            ->join('cycle', 'projects.cycle', '=', 'cycle.id')
            ->select(DB::raw('SUM(outcomes.score) as sum_of_outcome'))
            ->where('projects.user_id', '=', $u_id)
            ->where('cycle.status', '=', 'active')
            ->value('sum_of_outcome');



        $announcement = DB::table('announcement')
            ->select('*')
            ->where(function ($query) {
                $query->where('type', auth()->user()->type)
                    ->orWhere('type', 'all');
            })
            ->where('duedate', '>=', date("Y-m-d"))

            ->get();


        return view('userDetail', ['announcement' => $announcement, 'sumOfoutcome' => $sumOfoutcome, 'sumOfAvgScores' => $sumOfAvgScores, 'projects' => $projects, 'sum' => $sum, 'labels' => $labels, 'data' => $data, 'user' => $user, 'guage' => $guage]);
    }

    public function ajaxList($userid)
    {

        // $data = Project::select('old_project_id', 'title')->get();
        // return DataTables::of($data)
        //     ->addColumn('action', function ($row) {
        //         $btn = '<button class="btn btn-info btn-sm">View</button>';
        //         return $btn;
        //     })
        //     ->rawColumns(['action'])
        //     ->make(true);


        // Fetching limited records from final_report_grading per project


        // $data = Project::select('projects.*', 'final_report_grading.total')
        //     ->leftJoin('final_report_grading', 'projects.id', '=', 'final_report_grading.project_id')
        //     ->get();

        $data = Project::select(
            'projects.id',
            'projects.old_project_id',
            'projects.title',
            'cycle.cycle_title',
            'projects.status',
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(final_report_grading.total), ',', 1), ',', -1) AS total_1"),
            DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(GROUP_CONCAT(final_report_grading.total), ',', 2), ',', -1) AS total_2")
        )

            ->leftJoin('final_report_grading', 'projects.id', '=', 'final_report_grading.project_id')
            ->leftJoin('cycle', 'projects.cycle', '=', 'cycle.id')
            ->groupBy('projects.id', 'projects.old_project_id', 'projects.title', 'cycle.cycle_title', 'projects.status')
            ->where('projects.user_id',  '=', $userid)
            ->get();


        return DataTables::of($data)

            ->addColumn('action', function ($row) {
                $btn = '<button class="btn btn-info btn-sm">View</button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function userDetails()
    {
        $u_id = Auth::user()->id;
        $user = DB::table('users')->select('*')
            ->where('id', '=', $u_id)
            ->first();
        $projects = Project::leftJoin('grading_details', 'projects.id', '=', 'grading_details.project_id')
            ->select('projects.*', 'grading_details.reviewer_grade', 'grading_details.outcome_grade')
            ->where('projects.user_id', '=', $u_id)
            ->orderBy('projects.id', 'DESC')
            ->get();

        $sum = DB::table('projects', 'p')->join('cycle', 'p.cycle', '=', 'cycle.id')
            ->selectRaw('sum(p.total_score) as sum')
            ->groupBy(['p.user_id'])
            ->where('user_id', '=', $u_id)
            ->where('cycle.status', '=', 'active')
            ->pluck('p.sum');


        // $avg=json_decode($avg,true);
        //
        // dd((float)$avg);
        $sum = str_replace(array('[', ']', '"', '"'), '', $sum);

        //dd($avg);
        $cycle_score = DB::table('projects', 'p')
            ->selectRaw('p.cycle,avg(p.total_score) as avg')
            ->groupBy(['p.cycle'])
            ->where('user_id', '=', $u_id)
            ->orderBy('p.cycle', 'DESC')
            ->pluck('p.cycle', 'p.avg');
        $labels = $cycle_score->values();

        $data = $cycle_score->keys();
        $guage = DB::table('guage_settings')->find(1);

        $projects = Project::where('user_id', $u_id)->get();

        $projectIds = $projects->pluck('id')->toArray();

        // Fetching limited records from final_report_grading per project
        $limitedGradients = DB::table('final_report_grading')
            ->select('project_id', 'total')
            ->whereIn('project_id', $projectIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('project_id')
            ->map(function ($items) {
                return $items->take(2);
            });

        // Combine the project data and relevant gradients
        $combined = $projects->map(function ($project) use ($limitedGradients) {
            $project['gradients'] = $limitedGradients[$project['id']] ?? collect([]);
            return $project;
        });
        // dd($projects);

        //for gauge-1
        $sumOfAvgScores = DB::table('cycle')
            ->join('projects', 'cycle.id', '=', 'projects.cycle')
            ->select(DB::raw('SUM(projects.total_score) / COUNT(DISTINCT cycle.id) as sum_of_avg_scores'))
            ->where('user_id', '=', $u_id)
            ->where('cycle.status', '=', 'active')
            ->value('sum_of_avg_scores');

        //for gauge-2
        $sumOfoutcome = DB::table('projects')
            ->join('outcomes', 'projects.id', '=', 'outcomes.project_id')
            ->join('cycle', 'projects.cycle', '=', 'cycle.id')
            ->select(DB::raw('SUM(outcomes.score) as sum_of_outcome'))
            ->where('projects.user_id', '=', $u_id)
            ->where('cycle.status', '=', 'active')
            ->value('sum_of_outcome');

        //   dd($sumOfoutcome);

        return view('userDetail', ['sumOfoutcome' => $sumOfoutcome, 'sumOfAvgScores' => $sumOfAvgScores, 'projects' => $projects, 'sum' => $sum, 'labels' => $labels, 'data' => $data, 'user' => $user, 'guage' => $guage]);
    }



    public function settings()
    {
        return view('settings');
    }


    public function uploadedOutcomesDelete($id)
    {

        $outcome = DB::table('publication_detail')->where('outcome_id', $id)->first();

        if ($outcome) {
            DB::table('publication_detail')->where('outcome_id', $id)->delete();
        }

        $outcome = Outcome::find($id);

        if ($outcome) {
            $outcome->delete();
            return redirect()->back()->with('success', 'Outcome deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Outcome not found.');
        }
    }


    public function uploadedOutcomesDeleteStudent($id)
    {

        $outcome = DB::table('attached_students')->where('id', $id)->first();

        if ($outcome) {
            DB::table('attached_students')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Outcome deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Outcome not found.');
        }
    }


    public function uploadedOutcomesDeleteContribution($id)
    {

        $outcome = DB::table('contribution')->where('id', $id)->first();

        if ($outcome) {
            DB::table('contribution')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Outcome deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Outcome not found.');
        }
    }


    public function uploadedOutcomes(Request $request)
    {
        $commitments = DB::table('commitments')
            ->select('*')
            ->where('project_id', '=', $request->p_id)
            ->first();
        $outcomes = Outcome::leftJoin('publication_detail', 'outcomes.id', '=', 'publication_detail.outcome_id')
            ->select('outcomes.*', 'publication_detail.id as pid', 'publication_detail.outcome_id', 'publication_detail.title', 'publication_detail.publication_date', 'publication_detail.type', 'publication_detail.venue', 'publication_detail.url')
            ->where('project_id', '=', $request->p_id)
            ->get();

        //    dd($outcomes);

        $progress_report = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $request->p_id)
            ->where('submissions.type', '=', 'Progress')
            ->orderBy('submission_files.created_at', 'desc')
            ->first();
        $final_report = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $request->p_id)
            ->where('submissions.type', '=', 'Final')
            ->orderBy('submission_files.created_at', 'desc')
            ->first();

        $readiness_report = Submissions::leftJoin('submission_files', 'submissions.id', '=', 'submission_files.submission_id')
            ->select('submissions.*', 'submission_files.*')
            ->where('submissions.project_id', '=', $request->p_id)
            ->where('submissions.type', '=', 'Readiness')
            ->orderBy('submission_files.created_at', 'desc')
            ->first();

        $project = DB::table('projects')
            ->Join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->select('projects.*', 'cycle.cycle_title')
            ->where('projects.id', '=', $request->p_id)
            ->first();
        $contribution = DB::table('contribution')
            ->select('*')
            ->where('project_id', '=', $request->p_id)
            ->get();
        $students = DB::table('attached_students')
            ->select('*')
            ->where('project_id', '=', $request->p_id)
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

        return view('uploadedOutcome', ['progress_report' => $progress_report, 'final_report' => $final_report, 'readiness_report' => $readiness_report, 'commitments' => $commitments, 'outcomes' => $outcomes, 'project' => $project, 'contribution' => $contribution, 'typeMappings' => $typeMappings, 'students' => $students]);
    }
}
