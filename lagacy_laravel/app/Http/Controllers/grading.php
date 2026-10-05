<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ProgressReportGrading;
use App\Models\FinalReportGrading;
// use App\Models\FinalGradingDraft;
// use App\Models\ProgressGradingDraft;
use App\Models\Outcome;
use App\Models\GradingDetails;
use App\Models\User;
use App\Models\VerifyOutcomes;
use PDF;
use App\Models\Project;
// use Illuminate\Support\Carbon;
// use Illuminate\Support\Facades\Response;
// use GuzzleHttp\Client;
// use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Validator;


class Grading extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function finalGrades(Request $request)
    {
        $user = auth()->user()->name;
        if (($request->has('publish'))) {

            // dd('this is if part');
            $request->validate([
                'gradeA' => ['required', 'numeric'],
                'gradeB' => ['required', 'numeric'],
                'gradeC' => ['required', 'numeric', 'max:5', 'min:0'],
                'gradeD' => ['numeric'],

            ]);

            DB::transaction(function () use ($request) {

                if ($request->has('isAccepted')) {

                    $studentAccepted = $request->input('isAccepted');
                } else {

                    $studentAccepted = 0;
                }

                $attributes = [
                    'project_id' => $request->p_id,
                    'user_id' => auth()->user()->id,
                ];


                if ($request->gradeD === '-1') {
                    $sum = $request->gradeA + $request->gradeB + $request->gradeC;
                    $total = ($sum / 15) * 100;
                } else {
                    $sum = $request->gradeA + $request->gradeB + $request->gradeC + $request->gradeD;
                    $total = ($sum / 20) * 100;
                }
                $logged_in_user_id = auth()->user()->id;


                $grading = DB::table('final_report_grading')
                    ->select('*')
                    ->where('project_id', '=', $request->p_id)
                    ->first();


                $values = [
                    "gradeA" => $request->gradeA,
                    "commentA" => $request->commentA,
                    "gradeB" => $request->gradeB,
                    "commentB" => $request->commentB,
                    "gradeC" => $request->gradeC,
                    "commentC" => $request->commentC,
                    "gradeD" => $request->gradeD,
                    "commentD" => $request->commentD,
                    "project_id" => $request->p_id,
                    "user_id" => $logged_in_user_id,
                    "total" => $total,
                    "publish" => 'accepted',
                    "isaccepted" => $studentAccepted==="on"?1:0,
                    "isAdmin" => auth()->user()->type = 'Admin' ? 1 : 0,

                ];


                FinalReportGrading::updateOrCreate($attributes, $values);

                // first reviewer already has reviewed
                if ($grading) {
                    $score = ($total + $grading->total) / 2;
                    DB::update('update projects set total_score=? where id=?', [$score, $request->p_id]);
                }
            });

            return redirect()->back()->with('successfinalgrade', '<span class="alert alert-success" role="alert"> Final grades saved successfully </span>')->with('tab', 'FinalReport');
        } else {

            DB::transaction(function () use ($request) {

                $attributes = [
                    'project_id' => $request->p_id,
                    'user_id' => auth()->user()->id,
                ];


                if ($request->has('isAccepted')) {

                    $studentAccepted = $request->input('isAccepted');
                } else {

                    $studentAccepted = 0;
                }


                if ($request->gradeD === '-1') {
                    $sum = $request->gradeA + $request->gradeB + $request->gradeC;
                    $total = ($sum / 15) * 100;
                } else {
                    $sum = $request->gradeA + $request->gradeB + $request->gradeC + $request->gradeD;
                    $total = ($sum / 20) * 100;
                }
                $logged_in_user_id = auth()->user()->id;

                $values = [
                    "gradeA" => $request->gradeA,
                    "commentA" => $request->commentA,
                    "gradeB" => $request->gradeB,
                    "commentB" => $request->commentB,
                    "gradeC" => $request->gradeC,
                    "commentC" => $request->commentC,
                    "gradeD" => $request->gradeD,
                    "commentD" => $request->commentD,
                    "project_id" => $request->p_id,
                    "publish" => 'pending',
                    "isaccepted" => $studentAccepted==="on"?1:0,
                    "user_id" => $logged_in_user_id,
                    "total" => $total,

                ];

                FinalReportGrading::updateOrCreate($attributes, $values);
            });

            return redirect()->back()->with('successfinalgrade', '<span class="alert alert-warning" role="alert"> Final grades saved as draft successfully </span>')->with('tab', 'FinalReport');
        }
    }
    public function progressGrade(Request $request)
    {

        if ($request->has('publish')) {

            $validator = Validator::make($request->all(), [

                'studentsComments' => ['nullable', 'string', 'max:600'],
                'publicationsComments' => ['nullable', 'string', 'max:600'],
                'achievementsComments' => ['nullable', 'string', 'max:600']
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('tab', 'ProgressReport'); // Pass tab variable
            } else {



                DB::transaction(function () use ($request) {

                    $attributes = [
                        'project_id' => $request->p_id,
                        'user_id' => auth()->user()->id,
                    ];

                    // Define the attributes to update or create
                    $values = [
                        'analysis' => $request->analysis,
                        'comments' => $request->comments,
                        'recommendation' => $request->recommendation,
                        'path' => $request->path,
                        'publish' => 'accepted',
                        'ethical' => $request->ethical,
                        'achievementsRating' => $request->achievementsRating,
                        'publicationsRating' => $request->publicationsRating,
                        'studentsRating' => $request->studentsRating,
                        'achievementsComments' => $request->achievementsComments,
                        'publicationsComments' => $request->publicationsComments,
                        'studentsComments' => $request->studentsComments,
                        'budgetRating' => $request->budgetRating,
                        'budgetComments' => $request->budgetComments,
                        'isAccepted' => $request->isAccepted == "on" ? 1 : 0
                    ];

                    ProgressReportGrading::updateOrCreate($attributes, $values);

                    // ProgressReportGrading::create([
                    //     "analysis" => $request->analysis,
                    //     "comments" => $request->comments,
                    //     "recommendation" => $request->recommendation,
                    //     "path" => $request->path,
                    //     "user_id" => auth()->user()->id,
                    //     "project_id" => $request->p_id,
                    //     "publish" => 'pending',
                    //     "achievementsRating" => $request->achievementsRating,
                    //     "publicationsRating" => $request->publicationsRating,
                    //     "studentsRating" => $request->studentsRating,
                    //     "achievementsComments" => $request->achievementsComments,
                    //     "publicationsComments" => $request->publicationsComments,
                    //     "studentsComments" => $request->studentsComments,
                    //     "isAccepted" => $request->isAccepted == "on" ? 1 : 0
                    // ]);
                });


                // return view('home', ['message' => 'Progress Report comments and remarks have been upload successfully']);
                return redirect()->back()->with('successprogressgrade', '<span class="alert alert-success" role="alert"> Progress grades saved successfully </span>')->with('tab', 'ProgressReport');
            }
        } else {




            $validator = Validator::make($request->all(), [

                'studentsComments' => ['nullable', 'string', 'max:500'],
                'publicationsComments' => ['nullable', 'string', 'max:500'],
                'achievementsComments' => ['nullable', 'string', 'max:500']
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('tab', 'ProgressReport'); // Pass tab variable
            } else {
                DB::transaction(function () use ($request) {
                    $attributes = [
                        'project_id' => $request->p_id,
                        'user_id' => auth()->user()->id,
                    ];

                    // Define the attributes to update or create
                    $values = [
                        'analysis' => $request->analysis,
                        'comments' => $request->comments,
                        'recommendation' => $request->recommendation,
                        'path' => $request->path,
                        'publish' => 'pending',
                        'ethical' => $request->ethical,
                        'achievementsRating' => $request->achievementsRating,
                        'publicationsRating' => $request->publicationsRating,
                        'studentsRating' => $request->studentsRating,
                        'achievementsComments' => $request->achievementsComments,
                        'publicationsComments' => $request->publicationsComments,
                        'studentsComments' => $request->studentsComments,
                        'budgetRating' => $request->budgetRating,
                        'budgetComments' => $request->budgetComments,
                        'isAccepted' => $request->isAccepted == "on" ? 1 : 0
                    ];

                    ProgressReportGrading::updateOrCreate($attributes, $values);
                });
                return redirect()->back()->with('successprogressgrade', '<span class="alert alert-warning" role="alert"> Progress grades saved as draft successfully </span>')->with('tab', 'ProgressReport');
            }
        }
        // return view('home', ['message' => 'Progress Report Drafts has been upload successfully']);

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

    public function getProject($p_id)
    {
        $project = DB::table('projects')
            ->select('*')
            ->where('id', '=', $p_id)
            ->first();
    }
    public function statusUpdate(Request $request)
    {

        if (auth()->user()->type == User::TYPE_ADMIN) {
            $status = $request->status;
            $p_id = $request->p_id;
            DB::update('update projects set status=? where id=?', [$status, $p_id]);
            return route('project', ['message', 'Status Updated Successfully']);
        }
        return route('project');
    }
    public function index($p_id)
    {
        $progressGrades = ProgressReportGrading::leftJoin('users', 'progress_report_grading.user_id', '=', 'users.id')
            ->select('progress_report_grading.*', 'users.*')
            ->where('progress_report_grading.project_id', '=', $p_id)
            ->get();
        $finalGrades = FinalReportGrading::leftJoin('users', 'final_report_grading.user_id', '=', 'users.id')
            ->select('final_report_grading.*', 'users.*')
            ->where('final_report_grading.project_id', '=', $p_id)
            ->get();
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
            ->first();

        $progressGrades = json_decode($progressGrades, true);
        $finalGrades = json_decode($finalGrades, true);
        $pdf = PDF::loadView(
            'gradingDetails',
            [
                'finalGrades' => $finalGrades,
                'progressGrades' => $progressGrades,
                'project' => $project,
                'value' => '2',
                '
            p_id' => $p_id
            ]
        );

        return $pdf->download('sample.pdf');
    }
    public function verifyOutcomes(Request $request)
    {

        foreach ($request->all() as $key => $value) {
            if ($key !== '_token') {
                if ($key !== 'p_id') {
                    VerifyOutcomes::create([
                        "project_id" => $request->p_id,
                        "user_id" => auth()->user()->id,
                        "outcome_id" => $key,
                        "status" => $value,
                    ]);
                }
            }
        }
        $sum = VerifyOutcomes::leftJoin('outcomes', 'verify_outcome.outcome_id', '=', 'outcomes.id')
            ->selectRaw('sum(outcomes.score) as sum')
            ->where('outcomes.project_id', '=', $request->p_id)
            ->where('verify_outcome.project_id', '=', $request->p_id)
            ->toSql();
        dd($sum);
        $c = DB::table('commitments')
            ->select('*')
            ->where('project_id', '=', $request->p_id)
            ->first();
        //dd($c,$c->q1article,$c->q2article,$c->q3article,$c->q4article,$c->confArticle,$c->books,$c->editBooks,$c->chapters,$c->ip,$c->filedPatent,$c->openSourceSW,$c->startUp,$c->master,$c->UG,$c->Phd,$c->crossCollege);
        $expected_sum = ($c->q1article * 8) + ($c->q2article * 6) + ($c->q3article * 4) + ($c->q4article * 3) + ($c->confArticle * 2) + ($c->books * 8) + ($c->editBooks * 6) + ($c->chapters * 4) + ($c->ip * 4) + ($c->filedPatent * 7) + ($c->openSourceSW * 8) + ($c->startUp * 10) + ($c->master * 2) + ($c->UG) + ($c->Phd * 3) + ($c->crossCollege * 2);
        $gradeA = $sum->sum / $expected_sum;

        $gradeA = round($gradeA * 5, 2);
        FinalReportGrading::create([
            "project_id" => $request->p_id,
            "user_id" => auth()->user()->id,
            "gradeA" => $gradeA,
        ]);
        // dd($gradeA,$sum->sum,$expected_sum);
        return redirect('/grading/' . $request->p_id);
    }

    public function DBquery1()
    {
        $user_id = 1;
        dd(DB::table('projects', 'p')->join('outcomes', 'outcomes.project_id', '=', 'p.id')
            ->selectRaw('p.user_id,p.id,p.cycle,sum(outcomes.score) as sum')
            ->groupBy(['user_id', 'p.id', 'p.cycle'])
            // ->where('user_id','=',$user_id)
            ->get());
    }

    public function cycle_score()
    {
        $user_id = 1;
        dd(DB::table('projects', 'p')
            ->selectRaw('p.cycle,sum(p.total_score) as sum')
            ->groupBy(['p.cycle'])
            ->where('user_id', '=', $user_id)
            ->get());
    }

    public function DBquery()
    {
        $user_id = 1;
        dd(DB::table('projects', 'p')->join('cycle', 'p.cycle', '=', 'cycle.id')
            ->selectRaw('sum(p.total_score) as sum')
            ->groupBy(['p.user_id'])
            ->where('user_id', '=', $user_id)
            ->where('cycle.status', '=', 'active')
            ->get());
    }

    public function select(Request $request)
    {
        $p_id = $request->project_id;
        $project = DB::table('projects')
            ->select('*')
            ->where('id', '=', $p_id)
            ->first();
        $combinedArray = VerifyOutcomes::leftJoin('outcomes', 'verify_outcome.outcome_id', '=', 'outcomes.id')
            ->select('outcomes.id', 'outcomes.score', 'outcomes.online_date', 'outcomes.project_id', 'outcomes.type', 'outcomes.identifier', 'outcomes.score', 'outcomes.verifcation_by_reviewer', 'verify_outcome.*',)
            ->where('outcomes.project_id', '=', $p_id)
            ->get()
            ->groupBy('outcomes.project_id');


        foreach ($combinedArray as $items) {
            foreach ($items as $item) {
                if ($item['project_id'] == $p_id) {
                    $identifier = $item['identifier'];
                    $outcome_id = $item['outcome_id'];

                    if (!isset($outcomes[$identifier])) {
                        $outcomes[$identifier] = [];
                    }
                    if (!isset($outcomes[$identifier][$outcome_id])) {
                        $outcomes[$identifier][$outcome_id] = [
                            'verified' => [],
                            'not_verified' => [],
                            'type' => $item['type'], // add type
                            'outcome_id' => $outcome_id, // add outcome_id
                            'score' => $item['score'],
                            'online_date' => $item['online_date'],
                            'verifcation_by_reviewer' => $item['verifcation_by_reviewer'],
                        ];
                    }

                    if ($item['status'] == 'verified') {
                        $outcomes[$identifier][$outcome_id]['verified'][] = $item['user_id'];
                    } else {
                        $outcomes[$identifier][$outcome_id]['not_verified'][] = $item['user_id'];
                    }
                }
            }
        }

        if (isset($outcomes)) {
            $c = DB::table('commitments')
                ->select('*')
                ->where('project_id', '=', $p_id)
                ->first();
            //dd($c,$c->q1article,$c->q2article,$c->q3article,$c->q4article,$c->confArticle,$c->books,$c->editBooks,$c->chapters,$c->ip,$c->filedPatent,$c->openSourceSW,$c->startUp,$c->master,$c->UG,$c->Phd,$c->crossCollege);
            $expected_sum = ($c->q1article * 8) + ($c->q2article * 6) + ($c->q3article * 4) + ($c->q4article * 3) + ($c->confArticle * 2) + ($c->books * 8) + ($c->editBooks * 6) + ($c->chapters * 4) + ($c->ip * 4) + ($c->filedPatent * 7) + ($c->openSourceSW * 8) + ($c->startUp * 10) + ($c->master * 2) + ($c->UG) + ($c->Phd * 3) + ($c->crossCollege * 2);
            //dd($expected_sum);
            return view('outcomeStatus', ['outcomes' => $outcomes, 'project' => $project, 'expected_sum' => $expected_sum]);
        } else {
            return view('home', ['message' => 'no project outcomes found']);
        }
    }

    public function API_DOI()
    {

        $apiKey = '10.1186/s12880-021-00671-8';

        $endpoint = 'https://api.elsevier.com/content/search/scopus?query=DOI%28%22';

        $doi = ' 10.1016/j.promfg.2015.07.10'; // you can pass the doi key over here
        $query = 'DOI(' . $doi . ')';
        $count = 1;
        $start = 0;
        $sort = '';

        $url = 'https://api.elsevier.com/content/search/scopus?query=DOI%28%22' . $doi . '%22%29&apikey=7330487c0bfb556d2e47880b3b4e91c6';

        $response = file_get_contents($url);
        echo $response;
        $data = json_decode($response, true);

        if (isset($data['search-results']['entry'][0])) {
            $entry = $data['search-results']['entry'][0];
            // $journal = $entry['prism:publicationName'];
            // $year = substr($entry['prism:coverDate'], 0, 4);
            // $citedByCount = $entry['citedby-count'];
            // echo "Journal: $journal<br>";
            // echo "Year: $year<br>";
            // echo "Cited by count: $citedByCount<br>";
        } else {
            echo "No results found for DOI $doi";
        }
    }

    public function API()
    {
        return view('API');
    }


    private function elsevierAPI11($doi)
    {

        $client = new \GuzzleHttp\Client(['base_uri' => 'https://api.elsevier.com', 'verify' => false]);
        $response = $client->request('GET', "/content/metadata/article?apiKey=dba4a256db9b20b810b479c6f6730b8a&insttoken=77f5a53a2c01abd189666df2e091e8f4&query=doi($doi)");
        $res = json_decode($response->getBody()->getContents(), true);
        sleep(1);
        return ($res['search-results']['entry'][0]['prism:issn']);
        dd($res);
    }

    public function elsevierAPIii()
    {
        $doi = "12313131";
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://api.elsevier.com', 'verify' => false]);
        $response = $client->request('GET', "/content/metadata/article?apiKey=dba4a256db9b20b810b479c6f6730b8a&insttoken=77f5a53a2c01abd189666df2e091e8f4&query=doi($doi)");
        $res = json_decode($response->getBody()->getContents(), true);
        sleep(1);
        //return ($res['search-results']['entry'][0]['prism:issn']);
        if (isset($res['search-results']['entry'][0]['error'])) {
            dd($res['search-results']['entry'][0]['error']);
        }
        dd($res['search-results']['entry'][0] == "error");
    }
    public function elsevierAPI()
    {
        //$doi = "10.1016/j.jnca.2022.103332";
        $doi = "12313131";
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://api.elsevier.com', 'verify' => false]);
        $response = $client->request('GET', "/content/metadata/article?apiKey=dba4a256db9b20b810b479c6f6730b8a&insttoken=77f5a53a2c01abd189666df2e091e8f4&query=doi($doi)");
        $res = json_decode($response->getBody()->getContents(), true);
        sleep(1);
        //dd($res);
        if (isset($res['search-results']['entry'][0]['error'])) {
            //  dd($res);
            dd($res['search-results']['entry'][0]['error']);
            // dd($res['search-results']['entry'][0]['prism:issn']);
        }
        if (isset($res['search-results']['entry'][0]['prism:issn'])) {
            dd($res['search-results']['entry'][0]['prism:issn'], $res['search-results']['entry'][0]['available-online-date']);
        }
        dd($res['search-results']['entry'][0]['prism:issn']);
    }

    public function publish(Request $request)
    {

        $avg = DB::table('projects')->select('*')
            ->where('id', '=', $request->p_id)
            ->selectSub(function ($query) {
                $query->from('final_report_grading')
                    ->whereRaw('final_report_grading.project_id = projects.id')
                    ->selectRaw('avg(final_report_grading.total)');
            }, 'avg')
            ->where('projects.status', '=', 'Accepted')
            ->first();
        //dd($avg->avg, $request->p_id);
        if (auth()->user()->type === User::TYPE_ADMIN) {
            foreach ($request->all() as $key => $value) {
                if ($key !== '_token') {
                    if ($key !== 'p_id') {
                        Outcome::where('id', $key)
                            ->update([
                                'verifcation_by_reviewer' => $request[$key]
                            ]);
                    }
                }
            }
            $sum = DB::table('outcomes')
                ->selectRaw('sum(outcomes.score) as sum')
                ->where('outcomes.project_id', '=', $request->p_id)
                ->where('verifcation_by_reviewer', '=', 'verified')
                ->first();
            GradingDetails::create([
                "project_id" => $request->p_id,
                "user_id" => auth()->user()->id,
                "reviewer_grade" => $avg->avg,
                "outcome_grade" => $sum->sum
            ]);
            PRoject::where('id', $request->p_id)
                ->update([
                    'status' => 'Completed',
                    'total_score' => $avg->avg
                ]);
            $avg = DB::table('final_report_grading')
                ->selectRaw('avg(final_report_grading.total) as avg')
                ->where('id', '=', $request->p_id)
                ->get();
            //dd($avg);
            return view('Home', ['message' => 'Users grades updated successfully']);
        } else {
            return view('Home', ['message' => 'You are not authorised for this action']);
        }
    }

    public function CrossRefAPI()
    {
        $doi = "10.1186/s12880-021-00671-8";
        $client = new \GuzzleHttp\Client(['base_uri' => 'https://api.crossref.org/works/', 'verify' => false]);
        $response = $client->request('GET', "$doi");
        $res = json_decode($response->getBody()->getContents(), true);
        dd($res);
    }
}
