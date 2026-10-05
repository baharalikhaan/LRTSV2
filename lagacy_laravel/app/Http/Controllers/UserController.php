<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ReviewerGrading;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Mail\welcomeEmail;
use App\Models\Announcement;
use App\Models\UserPillar;
use App\Models\UserTag;
use Illuminate\Support\Facades\Auth;
use DataTables;
use Spatie\SimpleExcel\SimpleExcelWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Cycle;
use App\Models\FromConfTool;


class UserController extends Controller
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

    //Reviewer Agrements display

    public function ajaxreviewerAgrementsAdmin()
    {

        $data = DB::table('projects_reviewers')
            ->join('projects', 'projects.id', '=', 'projects_reviewers.project_id')
            ->join('users', 'users.id', '=', 'projects_reviewers.user_id')
            ->join('cycle', 'cycle.id', '=', 'projects.cycle')
            ->selectRaw('cycle.cycle_title,projects_reviewers.* ,projects.old_project_id,users.name,users.email')
            ->get();


        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $Btn1 =   null;
                $str = $row->cycle_title . '/' . $row->old_project_id . '-' . $row->email;
                $Btn1 = '<button onClick="showModal(\'' . $str . '\')" class="btn btn-teal btn-sm">View Agreement</button>';
                if ($row->proposalstatus == 'Accepted')
                    return  '<div class="btn-group" role="group" aria-label="Basic example">' . $Btn1 . '</div';
                else
                    return 'Not Available';
            })
            ->rawColumns(['action'])
            ->make(true);
    }



    public function reviewerDetail($u_id = null)
    {
        if ($u_id == null) {
            $u_id = Auth::user()->id;
        }
        $user = DB::table('users')
            ->select('*')
            ->where('users.id', '=', $u_id)
            ->first();

        $avg =    $data = DB::table('reviewer_grading')
            ->join('cycle', 'cycle.id', '=', 'reviewer_grading.cycle')
            ->select(DB::raw(' avg((conflict+responsiveness+comprehensiveness+no_reviewers+behaviour)/5) as total'))
            ->where('reviewer', '=', $u_id)
            ->first();

        $data = DB::table('reviewer_grading')
            ->join('cycle', 'cycle.id', '=', 'reviewer_grading.cycle')
            ->select(DB::raw('cycle.cycle_title,sum(conflict+responsiveness+comprehensiveness+no_reviewers+behaviour)/5 as total'))
            ->where('reviewer', '=', $u_id)
            ->groupBy('cycle_title')
            ->get();

        $data2 = DB::table('reviewer_grading')
            ->join('cycle', 'cycle.id', '=', 'reviewer_grading.cycle')
            ->select(DB::raw('cycle.cycle_title,conflict,responsiveness,comprehensiveness,no_reviewers,behaviour,
            scope_of_supply, mode_of_selection, basis_of_approval, type_extent_of_control, designation_of_approver'))
            ->where('reviewer', '=', $u_id)
            ->get();

        // Extracting labels and data from the $data collection
        $labels = $data->pluck('cycle_title')->toArray();
        $data = $data->pluck('total')->toArray();


        $guage = DB::table('guage_settings')->find(3);

        $projects = DB::table('projects')
            ->join('final_report_grading', 'projects.id', '=', 'final_report_grading.project_id')
            ->select(DB::raw('count(projects.id) as total'))
            ->where('final_report_grading.user_id', '=', $u_id)
            ->first();

        $cycles = DB::table('reviewer_grading')
            ->select(DB::raw('COUNT(cycle) as total'))
            ->where('reviewer', $u_id)
            ->first();


        $announcement = DB::table('announcement')
            ->select('*')
            ->where(function ($query) {
                $query->where('type', auth()->user()->type)
                    ->orWhere('type', 'all');
            })
            ->where('duedate', '>=', date("Y-m-d"))
            ->get();

        return view('reviewerDetail', ['announcement' => $announcement, 'data2' => $data2, 'cycles' => $cycles->total, 'projects' => $projects->total, 'avg' => $avg, 'data' => $data, 'labels' => $labels, 'user' => $user, 'guage' => $guage]);
    }

    public function downloadISO()
    {
        // Perform your database query to get users
        //  $users = User::all(); // Example query, you can modify it according to your needs

        $users = DB::table('reviewer_grading')
            ->join('users', 'users.id', '=', 'reviewer_grading.user_id')
            ->select(DB::raw('users.name,users.email,reviewer_grading.*'))->get();


        // Transform users data into array
        $userData = [];
        foreach ($users as $user) {
            $userData[] = [
                'Reviewer Name' => $user->name,
                'Reviewer Email' => $user->email,
                'Scope Of Supply' =>  $user->scope_of_supply,
                'Mode Of Selection' => $user->mode_of_selection,
                'Basis Of Appoval' =>   $user->basis_of_approval,
                'Type & Extent Of Control' =>    $user->type_extent_of_control,
                'Designation Of Approver' =>    $user->designation_of_approver

                // Add more fields as needed
            ];
        }

        // Create SimpleExcelWriter instance
        $writer = SimpleExcelWriter::create(storage_path('app/Reviewer_ISO_List.xlsx'));

        // Write data to Excel file
        $writer->addRows($userData);

        // Close the writer
        $writer->close();

        // Provide download link
        return response()->download(storage_path('app/Reviewer_ISO_List.xlsx'))->deleteFileAfterSend(true);
    }

    public function reviewerGrading($u_id)
    {


        $user = DB::table('users')
            ->select('*')
            ->where('users.id', '=', $u_id)
            ->first();

        // $cycles = DB::table('projects')
        //     ->select('cycle.*')
        //     ->join('cycle', 'projects.cycle', '=', 'cycle.id')
        //     ->join('progress_report_grading', 'projects.id', '=', 'progress_report_grading.project_id')
        //     ->join('final_report_grading', 'projects.id', '=', 'final_report_grading.project_id')
        //     ->distinct('cycle.id')
        //     ->where('final_report_grading.user_id', '=', $u_id)
        //     ->get();

        $cycles = DB::table('projects')
            ->select('cycle.*')
            ->join('cycle', 'projects.cycle', '=', 'cycle.id')
            ->join('projects_reviewers', 'projects_reviewers.project_id', '=', 'projects.id')
            ->distinct('cycle.id')
            ->where('projects_reviewers.user_id', '=', $u_id)
            ->get();


        $projects = DB::table('projects')
            ->join('final_report_grading', 'projects.id', '=', 'final_report_grading.project_id')
            ->select(DB::raw('count(projects.id) as total'))
            ->where('final_report_grading.user_id', '=', $u_id)
            ->first();

        $cycles2 = DB::table('reviewer_grading')
            ->select(DB::raw('COUNT(cycle) as total'))
            ->where('reviewer', $u_id)
            ->first();

        $avgrating = DB::table('reviewer_grading')
            ->select(DB::raw('(sum(conflict)+sum(responsiveness)+sum(comprehensiveness)+sum(no_reviewers)+sum(behaviour))/5 as avg'))
            ->where('reviewer', '=', $u_id)
            ->first();



        $avg = 0;
        if ($cycles2->total > 0)
            $avg = $avgrating->avg /  $cycles2->total;

        return view('reviewerGrading', ['avgrating' => $avg, 'cycles' => $cycles, 'projects' => $projects, 'user' => $user]);
    }

    public function saveratings(Request $request)
    {
        $data = $request->only([

            'conflict',
            'responsiveness',
            'comprehensiveness',
            'no_reviewers',
            'behaviour',
            'user_id'
        ]);

        // Assuming you have a combination of fields that uniquely identifies a record
        $uniqueKeys = [
            'reviewer' => $request->input('reviewer'),
            'cycle' => $request->input('cycle_id')
        ];

        // Find or create a record based on the unique key combination
        $reviewerGrading = ReviewerGrading::updateOrCreate(
            $uniqueKeys, // The unique key combination
            $data // The fields to update or create
        );
        return redirect()->back()->with('successrating', '<div class="alert alert-success" role="alert"> Rating set successfully</div>');
    }

    public function ajaxListreviewerGrading(Request $request)
    {
        $u_id = $request->input('user_id');
        $cycle_id = $request->input('cycle_id');
        // $projects = DB::table('projects')
        //     ->select(
        //         'cycle.id as cycleid',
        //         'cycle.cycle_title',
        //         'projects.id',
        //         'projects.title',
        //         'projects.old_project_id',
        //         'final_report_grading.gradeA',
        //         'final_report_grading.commentA',
        //         'final_report_grading.gradeB',
        //         'final_report_grading.commentB',
        //         'final_report_grading.gradeC',
        //         'final_report_grading.commentC',
        //         'final_report_grading.gradeD',
        //         'final_report_grading.commentD',
        //         'final_report_grading.total',
        //         'progress_report_grading.analysis',
        //         'progress_report_grading.comments',
        //         'progress_report_grading.recommendation',
        //         'progress_report_grading.project_id'
        //     )
        //     ->join('progress_report_grading', 'projects.id', '=', 'progress_report_grading.project_id')
        //     ->join('final_report_grading', 'projects.id', '=', 'final_report_grading.project_id')
        //     ->join('cycle', 'projects.cycle', '=', 'cycle.id')
        //     ->where('final_report_grading.user_id', '=', $u_id)
        //     ->where('projects.cycle', '=', $cycle_id)
        //     ->get();

        $projects = DB::table('projects')->Leftjoin('projects_reviewers', 'projects_reviewers.project_id', '=', 'projects.id')
            ->select(
                'projects_reviewers.proposalstatus',
                'projects_reviewers.created_at as deadline',
                'cycle.id as cycleid',
                'cycle.cycle_title',
                'projects.id',
                'projects.title',
                'projects.old_project_id',
                'final_report_grading.gradeA',
                'final_report_grading.commentA',
                'final_report_grading.gradeB',
                'final_report_grading.commentB',
                'final_report_grading.gradeC',
                'final_report_grading.commentC',
                'final_report_grading.gradeD',
                'final_report_grading.commentD',
                'final_report_grading.total',
                'progress_report_grading.*',


            )
            ->Leftjoin('progress_report_grading', 'projects.id', '=', 'progress_report_grading.project_id')
            ->Leftjoin('final_report_grading', 'projects.id', '=', 'final_report_grading.project_id')
            ->join('cycle', 'projects.cycle', '=', 'cycle.id')
            ->where('projects_reviewers.user_id', '=', $u_id)
            ->where('projects.cycle', '=', $cycle_id)
            ->get();

        //  dd($projects);

        $ratings = DB::table('reviewer_grading')
            ->select('*')
            ->where('reviewer', '=', $u_id)
            ->where('cycle', '=', $cycle_id)
            ->first();



        $data['projects'] = $projects;
        $data['ratings'] = $ratings;


        return $data;
    }

    public function ajaxList()
    {

        if (auth()->user()->type === User::TYPE_ADMIN) {
            $data = DB::table('users')
                ->select('id', 'name', 'username','email', 'type')
                ->get();
        }

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $Btn1 = $Btn2 = $Btn22 = null;
                $url1 = route('edit', ['id' => $row->id]);
                $Btn1 = '<a href="' . $url1 . '" class="btn btn-teal btn-sm">Edit</a>';

                $BtnFinal = '';
                if ($row->type == User::TYPE_LPI) {
                    $url2 = route('userDetail', ['u_id' => $row->id]);
                    $Btn2 = '<a href="' . $url2 . '" class="btn btn-warning btn-sm">LPI Details</a>';
                } else  if ($row->type == User::TYPE_REVIEWER) {


                    $url22 = route('reviewerDetail', ['u_id' => $row->id]);
                    $Btn22 = '<a href="' . $url22 . '" class="btn btn-warning btn-sm">Reviewer Details</a>';
                } else if ($row->type <> User::TYPE_ADMIN) {
                    $url2 = route('userDetail', ['u_id' => $row->id]);
                    $Btn2 = '<a href="' . $url2 . '" class="btn btn-warning btn-sm">LPI Details</a>';

                    $url22 = route('reviewerDetail', ['u_id' => $row->id]);
                    $Btn22 = '<a href="' . $url22 . '" class="btn btn-warning btn-sm">Reviewer Details</a>';
                }


                $BtnFinal =  $Btn2 . ' ' . $Btn22;



                $url3 = route('reviewerGrading', ['u_id' => $row->id]);
                $Btn3 = '<a href="' . $url3 . '" class="btn btn-teal btn-sm">Reviewer Rating</a>';
                if ($row->type == User::TYPE_ADMIN or $row->type == User::TYPE_LPI)
                    $Btn3 = '';

                return  '<div class="btn-group" role="group" aria-label="Basic example">' . $Btn1 . ' ' . $BtnFinal . ' ' . $Btn3 . '</div';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function display()
    {
        return view('user');
    }

    public function sortByname()
    {
        if (auth()->user()->type === User::TYPE_ADMIN) {
            $data = DB::table('users')
                ->select('*')
                ->orderBy('name')
                ->get();
            $data = json_decode($data, true);
            return view('user', ['users' => $data]);
        } else {
        }
    }
    public function searchByUser(REQUEST $request)
    {
        if (auth()->user()->type === User::TYPE_ADMIN) {
            $data = DB::table('users')
                ->select('*')
                ->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->get();
            $data = json_decode($data, true);
            return view('user', ['users' => $data]);
        } else {
        }
    }

    public function sortByemail()
    {
        if (auth()->user()->type === User::TYPE_ADMIN) {
            $data = DB::table('users')
                ->select('*')
                ->orderBy('email')
                ->get();
            $data = json_decode($data, true);
            return view('user', ['users' => $data]);
        } else {
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function new()
    {
        $pillars = DB::table('pillars')
            ->select(DB::raw('MIN(id) as id'), 'pillar')
            ->groupBy('pillar')
            ->get();
        $tags = DB::table('tags')
            ->get();
        return view('auth.register', ['pillars' => $pillars, 'tags' => $tags]);
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

        $pillars = DB::table('pillars')->distinct('pillars')->get();

        if (auth()->user()->type === User::TYPE_ADMIN) {

            $pillars = DB::table('pillars')
                ->select(DB::raw('MIN(id) as id'), 'pillar')
                ->groupBy('pillar')
                ->get();

            $tags = DB::table('tags')
                ->get();

            $user = DB::table('users')->Leftjoin('user_pillars', 'users.id', '=', 'user_pillars.user_id')
                ->Leftjoin('users_tags', 'users.id', '=', 'users_tags.user_id')
                ->select('users.id', 'email', 'name', 'username', 'type', 'roles', 'user_pillars.pillar_id', 'users_tags.tag_id')
                ->where('users.id', '=', $id)
                ->first();
            //    dd($user);

            return view('updateUser', ['user' => $user, 'pillars' => $pillars, 'tags' => $tags]);
        } else
            return view('home', ['You do not have access this action']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'type' => ['required'],
            'username' => ['required'],

            'pillar' => ['required'],
            'tag' => ['required'],
            'faculty' => ['required']

        ]);

        DB::transaction(function () use ($request) {
            $user = User::find($request->userid);
            $user->name = $request->input('name');
            $user->username = $request->input('username');
            $user->userid = $request->input('username');
            $user->type = $request->input('type'); //explode('+', $request->input('type'))[0];
            $user->roles = $request->input('type');
            $user->email = $request->input('email');
            $user->faculty = $request->input('faculty');
            $user->update();


            $userPillar = UserPillar::where('user_id', $request->userid)->first();

            if ($userPillar) {
                $userPillar->pillar_id = $request->pillar;
                $userPillar->save();
            } else {
                UserPillar::create([
                    "user_id" => $user->id,
                    "pillar_id" => $request->pillar,
                ]);
            }


            $userTag = UserTag::where('user_id', $request->userid)->first();

            if ($userTag) {
                $userTag->tag_id = $request->tag;
                $userTag->save();
            } else {
                UserTag::create([
                    "user_id" => $user->id,
                    "tag_id" => $request->tag,
                ]);
            }
        });
        //   return view('home', ['message' => 'User updated successfully']);

        return redirect()->back()->with('successuser', '<div class="alert alert-success" role="alert"> User updated successfully.</div>');
    }

    protected function validator(Request $request)
    {
        return Validator::make($request, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'type' => ['required'],
            'faculty' => ['required'],
            'pillar' => ['required'],
        ]);
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

    public function nationality()
    {

        return view('auth.nationality', ['user' => Auth::user()]);
    }


    protected function updateNationality(Request $request)
    {
        $request->validate([
            'nationality' => ['required'],
        ]);

        $user = User::find(Auth::user()->id);
        $user->nationality = $request->input('nationality');
        $user->update();
        return redirect()->route('home');
    }

    public function verifyUsersPost(Request $request)
    {
        //Excel import
        // if ($request->hasFile('excel')) {
        //     $path = $request->file('excel')->getRealPath();
        //     $spreadsheet = IOFactory::load($path);
        //     $sheet = $spreadsheet->getActiveSheet();
        //     $data = $sheet->toArray();

        //     $emailsNotFound =  array();
        //     $emailsNeedUpdated = array();

        //     // Iterate through the data (assuming the first sheet contains the emails in the 5th column)
        //     foreach (array_slice($data, 1) as $row) {
        //         if ($row[5] <> null) {
        //             $email = isset($row[5]) ? $row[5] : null;
        //             if ($email) {
        //                 $user = User::where('email', $email)->first();
        //                 if (!$user) {
        //                     $emailsNotFound[] = $email;
        //                 } else {
        //                     $user = User::whereNotIn('roles', ['LPI', 'LPI+Reviewer', 'Admin+LPI'])
        //                         ->where('email', $email)
        //                         ->first();
        //                     $emailsNeedUpdated[] =  $email;
        //                 }
        //             }
        //         }
        //     }


        //     $response['EmailNotFound'] =      json_encode($emailsNotFound);
        //     $response['EmailFound'] =      json_encode($emailsNeedUpdated);

        if ($request->hasFile('excel')) {
            $path = $request->file('excel')->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            $emailsNotFound = [];
            $emailsNeedUpdated = [];

            // Iterate through rows, skipping the first row (header)
            foreach (array_slice($data, 1) as $row) {
                foreach ($row as $cell) {
                    if (filter_var($cell, FILTER_VALIDATE_EMAIL)) { // Check if cell contains a valid email
                        $email = trim($cell);
                        $user = User::where('email', $email)->first();

                        if (!$user) {
                            $emailsNotFound[] = $email;
                        } else {
                            $userCheck = User::whereNotIn('roles', ['LPI', 'LPI+Reviewer', 'Admin+LPI'])
                                ->where('email', $email)
                                ->first();

                            if ($userCheck) {
                                $emailsNeedUpdated[] = $email;
                            }
                        }
                    }
                }
            }

            $response['EmailNotFound'] = json_encode($emailsNotFound);
            $response['EmailFound'] = json_encode($emailsNeedUpdated);
        }

        return redirect()->back()->with('file_response',  $response);
    }

    protected function create(Request $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'type' => ['required'],
            // 'type' => 'required|array',
            // 'type.*' => 'string|in:Admin,LPI,Reviewer',
            'pillar' => ['required'],
            'tag' => ['required'],
            'faculty' => ['required']

        ]);
        if (auth()->user()->type === User::TYPE_ADMIN) {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'username' => $request['username'],
                    'userid' => $request['username'],
                    'type' => $request->input('type'), //explode('+', $request->input('type'))[0],
                    'roles' => $request['type'], // implode('+',  $request['type']),
                    'faculty' => $request['faculty'],
                ]);
                UserPillar::create([
                    "user_id" => $user->id,
                    "pillar_id" => $request['pillar'],
                ]);

                UserTag::create([
                    "user_id" => $user->id,
                    "tag_id" => $request['tag'],
                ]);

                //send email to newly created user.
                $ctrl = new EmailController();
                $ctrl->user_added($user);
            });

            return redirect()->back()->with('successuser', '<div class="alert alert-success" role="alert"> User has been registered successfully. An email has been sent to the target user.</div>');
        } else {
            return redirect()->back()->with('successuser', '<p style="color: red;"> You done not have the authority</p>');
        }
    }
}
