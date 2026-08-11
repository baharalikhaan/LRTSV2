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
use App\Models\Tags;
use App\Models\Pillars;
use App\Models\FromConfTool;
use App\Models\studentgrant_students;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\ProjectPillar;
use App\Models\ProjectTag;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        //   dd(Auth::user());
        //  $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function ajaxList3()
    {
        $data = DB::table('from_conf_tool')
            ->join('cycle', 'cycle.id', '=', 'from_conf_tool.cycle')
            ->select('from_conf_tool.*', 'cycle.*')
            ->get();

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $projectId = $row->old_project_id;
                $cycle = $row->cycle_title;

                $types = [
                    'ethical_approvals'     => 'Ethical Approval',
                    'final_reports'         => 'Final Report',
                    'progress_reports'      => 'Progress Report',
                    'lpi_project_proposals' => 'LPI Proposal',
                    'readiness_reports'     => 'Readiness Report',
                ];

                $zipUrl = route('download.zip', [
                    'cycle' => $cycle,
                    'projectId' => $projectId,
                ]);

                return '<a href="' . $zipUrl . '" class="btn btn-sm btn-outline-success">
                    <i class="fa-solid fa-file-zipper me-2"></i>Download All
                  </a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function downloadZip($cycle, $projectId)
    {
        $types = [
            'ethical_approvals'     => 'Ethical Approval',
            'final_reports'         => 'Final Report',
            'progress_reports'      => 'Progress Report',
            'lpi_project_proposals' => 'LPI Proposal',
            'readiness_reports'     => 'Readiness Report',
        ];

        $zip = new \ZipArchive;
        $zipFileName = storage_path("app/temp/{$projectId}_$cycle.zip");

        // Ensure temp directory exists
        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        if ($zip->open($zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Failed to create ZIP file. Please try again later.');
        }

        $fileAdded = false;

        foreach ($types as $folder => $label) {
            $pdfFilename = $folder === 'lpi_project_proposals'
                ? "$projectId.pdf"
                : "$projectId.pdf";

            $basePath = "uploads/$folder/$cycle";
            $pdfPath = "$basePath/$pdfFilename";
            $folderPath = "$basePath/$projectId";

            // Add single PDF if it exists
            if (Storage::disk('local')->exists($pdfPath)) {
                $zip->addFile(storage_path("app/$pdfPath"), "$label/$pdfFilename");
                $fileAdded = true;
            }

            // Add all files in subfolder (recursively)
            if (Storage::disk('local')->exists($folderPath) && is_dir(storage_path("app/$folderPath"))) {
                $allFiles = File::allFiles(storage_path("app/$folderPath"));
                foreach ($allFiles as $fileObj) {
                    $relativePathInFolder = str_replace(storage_path("app/$folderPath"), '', $fileObj->getPathname());
                    $relativePathInFolder = ltrim($relativePathInFolder, DIRECTORY_SEPARATOR);

                    $zip->addFile($fileObj->getPathname(), "$label/$projectId/$relativePathInFolder");
                    $fileAdded = true;
                }
            }
        }

        $zip->close();

        if (!$fileAdded) {
            if (file_exists($zipFileName)) {
                unlink($zipFileName);
            }
            return redirect()->back()->with('error', 'No files found to zip for this project.');
        }

        if (!file_exists($zipFileName)) {
            return redirect()->back()->with('error', 'ZIP file could not be created.');
        }

        return response()->download($zipFileName)->deleteFileAfterSend(true);
    }


    public function ajaxList(Request $request)
    {
        $cycle = $request->input('cycle'); // receive from DataTables

        $user = auth()->user();
        $type = auth()->user()->type;
        if ($type == User::TYPE_ADMIN) {
            $data = DB::table('from_conf_tool')
                ->Join('cycle', 'cycle.id', '=', 'from_conf_tool.cycle')
                ->select('from_conf_tool.*', 'cycle.grant_type')
                ->where('cycle.grant_type', '=', 'regular')
                ->where('added', '=', '0')
                ->where('cycle', '=', $cycle)
                ->get();
        } else {
            $data = DB::table('from_conf_tool')
                ->Join('cycle', 'cycle.id', '=', 'from_conf_tool.cycle')
                ->select('from_conf_tool.*', 'cycle.grant_type')
                ->where('email', '=', $user->email)
                ->where('added', '=', '0')
                ->where('cycle.grant_type', '=', 'regular')
                ->where('cycle', '=', $cycle)
                ->get();
        }
        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $type = auth()->user()->type;
                $Btn =    null;
                if ($row->added == 0) {
                    $url1 = route('newProject', ['p_id' => $row->id]);
                    $Btn = '<a href="' . $url1 . '" class="btn btn-teal btn-sm">Register Project</a>';
                }
                return   $Btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function switchRole($role)
    {
        $user = Auth::user();
        if ($user) {
            $user->type = $role;
            $user->save();
            Auth::Logout();
            Auth::Login($user);
        }
        return $this->index();
    }

    public function displaycycle()
    {
        $type = auth()->user()->type;
        return view('Cycleconfprojects', ['type' => $type]);
    }

    public function ajaxListconfcycle(Request $request)
    {
        $cycle = $request->input('cycle'); // receive from DataTables

        $type = auth()->user()->type;
        $user = auth()->user()->email;

        if ($type == User::TYPE_ADMIN) {
            $data = Cycle::join('from_conf_tool', 'from_conf_tool.cycle', '=', 'cycle.id')
                ->select('cycle.*', DB::raw('COUNT(from_conf_tool.id) as total'))
                ->where('from_conf_tool.added', '=', 0)
                ->groupBy('cycle.id')
                ->get();
        }
        else if ($type == User::TYPE_LPI) {
            $data = Cycle::join('from_conf_tool', 'from_conf_tool.cycle', '=', 'cycle.id')
                ->select('cycle.*', DB::raw('COUNT(from_conf_tool.id) as total'))
                ->where('from_conf_tool.added', '=', 0)
                ->where('from_conf_tool.email', '=', $user)
                ->groupBy('cycle.id')
                ->get();
        }

        // Apply cycle filter if provided
        if (!empty($cycle)) {
            $data->where('cycle.id', $cycle);
        }

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('confprojects', ['c_id' => $row->id]);
                return '<a href="' . $url . '" class="btn btn-teal btn-sm">View Projects</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function confprojects(Request $request)
    {
        $cycle = $request->input('c_id');
        $cycles = Cycle::join('from_conf_tool', 'from_conf_tool.cycle', '=', 'cycle.id')
            ->select('cycle.*', DB::raw('COUNT(from_conf_tool.id) as total'))
            ->where('cycle.id', '=', $cycle)
            ->where('from_conf_tool.added', '=', 0)
            ->first();

        $user = Auth::user();
        return view('confprojects', ['user' => $user, 'cycle' => $cycle, 'cycles' => $cycles]);
    }


    public function index(Request $request = null)
    {
            //     /************************** bypass login ***********************/
      $user = User::where('email', 'hn2000131@qu.edu.qa')->first();
     //       $user = User::where('email', 'benamor.abdelbaki@qu.edu.qa')->first();
 //$user = User::where('email', 'abdelaziz.bouras@qu.edu.qa')->first();

        if ($user) {
            Auth::logout();
            Auth::login($user);
        } else {
        }

        /****************************************************************/


        $user = auth()->user();

        if ($user) {
            if ($user->type === User::TYPE_ADMIN) {
                return redirect()->route('dashboard');
            } elseif ($user->type === User::TYPE_LPI) {
                return redirect()->route('userDetails');
            } elseif ($user->type === User::TYPE_REVIEWER) {
                return redirect()->route('reviewerDetails');
            }
        } else {
            $AboutUs = DB::table('about_us')
                ->select('*')
                ->get();
            $AboutUs = json_decode($AboutUs, true);
            return view('welcome', ['about' => $AboutUs]);
        }
    }

    public function announcementDetail($id)
    {
        $announcement = DB::table('announcement')
            ->select('*')
            ->where('id', '=', $id)
            ->first();
        return view('announcementDetail', ['announcement' => $announcement]);
    }

    public function announcementSetting()
    {
        $announcements = DB::table('announcement')
            ->select('*')
            ->get();
        $announcements = json_decode($announcements, true);

        return view('announcementSetting', ['announcements' => $announcements]);
    }
    public function announcementEdit($id)
    {
        $announcement = DB::table('announcement')
            ->select('*')
            ->where('id', '=', $id)
            ->first();
        return view('announcementEdit', ['announcement' => $announcement]);
    }

    public function announcementUpdate($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string', 'max:500'],
            'duedate' => ['required'],
            'image' => ['required', 'file', 'mimes:png,jpg,jpeg']
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput(); // Pass tab variable
        } else {

            $imageUrl = '';
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName =  time() . '.' . $image->getClientOriginalName();
                $image->move(public_path('uploads'), $imageName);
                $imageUrl = url('uploads/' . $imageName);
            }


            $announcement = Announcement::find($id);
            $announcement->content = $request->input('content');
            $announcement->subject = $request->input('subject');

            $announcement->type = $request->input('type');
            $announcement->duedate = $request->input('duedate');
            $announcement->image =  $imageUrl;

            $announcement->update();
            $announcements = DB::table('announcement')
                ->select('*')
                ->get();
            $announcements = json_decode($announcements, true);
            return view('announcementSetting', ['announcements' => $announcements]);
        }
    }

    public function aboutUsSettings()
    {
        $about = DB::table('about_us')
            ->select('*')
            ->get();
        $about = json_decode($about, true);
        return view('aboutUsSettings', ['about' => $about]);
    }
    public function aboutUsEdit($id)
    {
        $about = DB::table('about_us')
            ->select('*')
            ->where('id', '=', $id)
            ->first();
        return view('aboutUsEdit', ['about' => $about, 'file' => '/serveFile']);
    }

    public function welcome()
    {
        $user = auth()->user();
        $AboutUs = DB::table('about_us')
            ->select('*')
            ->get();
        $AboutUs = json_decode($AboutUs, true);
        return view('welcome', ['about' => $AboutUs]);
    }

    public function cycle()
    {
        $cycle = DB::table('cycle')
            ->select('*')
            ->get();
        $cycle = json_decode($cycle, true);
        return view('cycle', ['cycle' => $cycle]);
    }



    public function ajaxListAboutus()
    {
        $type = auth()->user()->type;
        $user = auth()->user()->id;
        if ($type == User::TYPE_ADMIN) {
            $data = DB::table('about_us')
                ->select('*')
                ->get();
        }

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('aboutUsEdit', ['id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Edit</a>';
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
            $data = DB::table('cycle')
                ->select('*')
                ->get();
        }

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('cycleEdit', ['id' => $row->id]);
                $url2 = route('conftoolProjects', ['cycle' => $row->id]);
                $url3 = route('deleteCycle', ['id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Edit</a>';
                $Btn2 = '<a href="' . $url2 . '" class="btn btn-teal btn-sm">Conf-Tool Projects</a>';
                $Btn2 = $row->grant_type == 'regular' ? $Btn2 : '';
                $Btn3 = '';
                return   $Btn . ' ' . $Btn2 . ' ' . $Btn3;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function ajaxListAnnouncement()
    {
        $type = auth()->user()->type;
        $user = auth()->user()->id;
        if ($type == User::TYPE_ADMIN) {
            $data = DB::table('announcement')
                ->select('*')
                ->get();
        }
        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('announcementEdit', ['id' => $row->id]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Edit</a>';
                $url2 = route('announcementDetail', ['id' => $row->id]);
                $Btn .= ' <a href="' . $url2 . '" class="btn btn-teal btn-sm">Details</a>';

                return   $Btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function AboutUsUpdate($id, Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'image',
            'role' => 'required',
            'introduction' => 'required',
            'email' => 'required',

        ]);
        $abt = AboutUS::find($id);
        if ($request->has('image')) {
            $path = Storage::putFile('images', $request->file('image'));

            $path = substr($path, 7);
            $path = 'serveImg?file=' . $path;
            $abt->path = $path;
        }
        $abt->name = $request->input('name');
        $abt->role = $request->input('role');
        $abt->introduction = $request->input('introduction');
        $abt->email = $request->input('email');
        $abt->update();
        $about  = DB::table('about_us')
            ->select('*')
            ->get();
        $about  = json_decode($about, true);
        return view('welcome', ['about' => $about]);
    }

    public function serveFile()
    {
        $half = request()->get('file');
        $path = '../storage/app/images/' . $half;
        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }
    public function cycleEdit($id)
    {
        $cycle = DB::table('cycle')
            ->select('*')
            ->where('id', '=', $id)
            ->first();
        return view('cycleEdit', ['cycle' => $cycle]);
    }

    public function deleteCycle($id)
    {
        $cycle = DB::table('cycle')
            ->select('*')
            ->where('id', '=', $id)
            ->first();

        if ($cycle) {
            $extractPath = storage_path('app' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'lpi_project_proposals' . DIRECTORY_SEPARATOR . $cycle->cycle_title . DIRECTORY_SEPARATOR);

            $this->deleteFolder($extractPath);

            if ($cycle->grant_type == 'regular') {
            } else {
            }
        }
    }
    function deleteFolder($folderPath)
    {
        if (!file_exists($folderPath)) {
            return false;
        }

        foreach (scandir($folderPath) as $file) {
            if ($file == '.' || $file == '..') continue;

            $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;

            if (is_dir($filePath)) {
                $this->deleteFolder($filePath);
            } else {
                unlink($filePath);
            }
        }

        return rmdir($folderPath);
    }

    public function cycleUpdate($id, Request $request)
    {
        $cycle = Cycle::find($id);
        if ($request->has('prog_rpt_deadline'))
            $cycle->prog_rpt_deadline = $request->input('prog_rpt_deadline');
        if ($request->has('final_rpt_deadline'))
            $cycle->final_rpt_deadline = $request->input('final_rpt_deadline');
        if ($request->has('extended_prog_rpt_deadline'))
            $cycle->extended_prog_rpt_deadline = $request->input('extended_prog_rpt_deadline');
        if ($request->has('extended_final_rpt_deadline'))
            $cycle->extended_final_rpt_deadline = $request->input('extended_final_rpt_deadline');
        if ($request->has('prog2_rpt_deadline'))
            $cycle->prog2_rpt_deadline = $request->input('prog2_rpt_deadline');
        if ($request->has('extended_prog2_rpt_deadline'))
            $cycle->extended_prog2_rpt_deadline = $request->input('extended_prog2_rpt_deadline');
        if ($request->has('status'))
            $cycle->status = $request->input('status');
        if ($request->has('upload_outcomes'))
            $cycle->upload_outcomes = $request->input('upload_outcomes');
        $cycle->update();
        $cycle  = DB::table('cycle')
            ->select('*')
            ->get();
        $cycle  = json_decode($cycle, true);
        return view('cycle', ['cycle' => $cycle]);
    }

    public function newCycle()
    {
        $cycle = DB::table('cycle')
            ->selectRaw('max(id) as cycle')
            ->first();
        $cycle = (int)($cycle->cycle) + 1;
        return view('newCycle', ['cycle' => $cycle]);
    }
    public function createCycle(Request $request)
    {

        $request->validate([
            'cycle' => 'required',
            'cycle_title' => 'required',
            'prg_rpt_deadline' => 'required',
            'final_rpt_deadline' => 'required',
            'pdf' => 'required',
            'excel' => 'required'
        ]);

        //Import pdf
        $path = $request->file('pdf')->getRealPath();
        if ($request->hasFile('pdf')) {
            $zipFile = $request->file('pdf');
            $fileName = $zipFile->getClientOriginalName();

            $zipFile->storeAs('uploads/lpi_project_proposals/' . $request->cycle_title . '/', $fileName);
            $extractPath = storage_path('app' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'lpi_project_proposals' . DIRECTORY_SEPARATOR . $request->cycle_title . DIRECTORY_SEPARATOR);
            $zip = new \ZipArchive;
            if ($zip->open($extractPath . $fileName) === TRUE) {

                $zip->extractTo($extractPath);
                $zip->close();
            }
            File::delete(storage_path('app/uploads/lpi_project_proposals/' . $request->cycle_title . '/' . $fileName));
        }

        $arr = array();
        //Excel import
        if ($request->hasFile('excel')) {
            $path = $request->file('excel')->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            $excelHeaders = array_shift($data);

            //if its regular project
            if (count($excelHeaders) == 12) {

                $id = Cycle::updateOrCreate(
                    ['cycle_title' => $request->cycle_title],
                    [
                        "id" => $request->cycle,
                        "prog_rpt_deadline" => $request->prg_rpt_deadline,
                        "extended_prog_rpt_deadline" => $request->extended_prg_rpt_deadline,
                        "prog2_rpt_deadline" => $request->prog2_rpt_deadline,
                        "extended_prog2_rpt_deadline" => $request->extended_prog2_rpt_deadline,
                        "final_rpt_deadline" => $request->final_rpt_deadline,
                        "extended_final_rpt_deadline" => $request->extended_final_rpt_deadline,
                        "grant_type" => $request->grant_type
                    ]
                );

                foreach (array_slice($data, 0) as $row) {
                    if ($row[0] <> null) {
                        $usr = User::where('email',  $row[5])->value('id');
                        if (!$usr) {
                            $arr[] = $row[5];
                            continue;
                        }
                        $confid =   FromConfTool::updateOrCreate(['title' => $row[3]], [
                            'id' => null,
                            'old_project_id' => $row[1],
                            'cycle' => $id->id,
                            'title' =>  $row[3],
                            'author' => $row[4],
                            'email' => $row[5],
                            'added' => $row[6],
                            'created_at' => $row[7],
                            'updated_at' => $row[8],
                            'pillars' => $row[9],
                            'tags' => $row[10],
                            'grant_type' => $row[11],
                        ]);
                    }
                }
            }

            //if it is student grant
            else   if (count($excelHeaders) == 19) {
                $id = Cycle::updateOrCreate(
                    ['cycle_title' => $request->cycle_title],
                    [
                        "id" => $request->cycle,
                        "prog_rpt_deadline" => $request->prg_rpt_deadline,
                        "extended_prog_rpt_deadline" => $request->extended_prg_rpt_deadline,
                        "prog2_rpt_deadline" => $request->prog2_rpt_deadline,
                        "extended_prog2_rpt_deadline" => $request->extended_prog2_rpt_deadline,
                        "final_rpt_deadline" => $request->final_rpt_deadline,
                        "extended_final_rpt_deadline" => $request->extended_final_rpt_deadline,
                        "grant_type" => $request->grant_type
                    ]
                );


                foreach (array_slice($data, 0) as $row) {
                    if ($row[0] <> null) {


                        $usr = User::where('email',  $row[5])->value('id');
                        if (!$usr) {
                            $arr[] = $row[5];
                            continue;
                        }


                        //add to conf tool
                        $confid =   FromConfTool::updateOrCreate(['old_project_id' => $row[1], 'cycle' => $id->id], [
                            'id' => null,
                            'old_project_id' => $row[1],
                            'cycle' => $id->id,
                            'title' =>  $row[8],
                            'author' => $row[4],
                            'email' => $row[5],
                            'added' => 1,
                            'created_at' => null,
                            'updated_at' => null,
                            'pillars' => $row[9],
                            'tags' => $row[7],
                            'grant_type' => 'student'
                        ]);

                        //add to projects



                        $projid =    Project::updateOrCreate(['old_project_id' => $row[1], 'cycle' => $id->id], [
                            'id' => null,
                            'old_project_id' => $row[1],
                            'conf_tool_id' => $confid->id,
                            'title' =>  $row[8],
                            'status' => 'Accepted',
                            'user_id' => $usr,
                            'cycle' => $id->id,
                            'requested_budget_qar' => $row[15],
                            'college_decision' => $row[16],
                            'rsd_feedback' => $row[17],
                            'final_rsd_decision' => $row[18]
                        ]);

                        //project pillars
                        $pillarId = Pillars::where('subpillar',  $row[9])->value('id');
                        if ($pillarId)
                            ProjectPillar::updateOrCreate(['project_id' => $projid->id, 'pillar_id' => $pillarId], [
                                'project_id' =>    $projid->id,
                                'pillar_id'  => $pillarId
                            ]);

                        //project tags
                        $tagid = Tags::where('tag',  $row[7])->value('id');
                        if ($tagid)
                            ProjectTag::updateOrCreate(['project_id' => $projid->id, 'tag_id' => $tagid], [
                                'project_id' =>    $projid->id,
                                'tag_id'  => $tagid
                            ]);

                        //get all the students in the grant
                        $array = array_map('trim', explode(",", $row[12]));
                        foreach ($array as $std) {
                            //get info from API and dump into db
                            $client = new Client();
                            $stdid =  (string) $std;

                            $json = $client->request('GET', 'http://quapxweb1.qu.edu.qa/sisapx/qusis/student_info/std', [
                                'headers' => [
                                    'sec_key' => 'STD@R',
                                    'st_id' =>  $stdid,
                                ]
                            ]);
                            $jsonString = $json->getBody()->getContents();

                            $data = json_decode($jsonString, true);

                            if (isset($data['items']) && is_array($data['items'])) {

                                // Get the last item from the array
                                $item = end($data['items']);

                                if (is_array($item)) {
                                    $student_id = $item['student_id'];
                                    $first_name = $item['first_name'];
                                    $last_name = $item['last_name'];
                                    $student_status = $item['student_status'];
                                    $major = $item['major'];
                                    $minor = $item['minor'];
                                    $college = $item['college'];
                                    $std_program = $item['std_program'];
                                    $std_level = $item['std_level'];
                                    $admission_term = $item['admission_term'];
                                    $reg_in_course = $item['reg_in_course'];
                                }
                                studentgrant_students::updateOrCreate(['project_id' => $projid->id, 'email' => $row[5], 'student_id' =>  $stdid], [

                                    'project_id' => $projid->id,
                                    'email' => $row[5],
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'student_id' => $stdid,
                                    'nationality' => $row[13],
                                    'student_status' =>  $student_status,
                                    'major' => $major,
                                    'minor' => $minor,
                                    'std_program' => $std_program,
                                    'std_level' => $std_level,
                                    'admission_term' => $admission_term,
                                    'reg_in_course' => $reg_in_course,
                                    'college' => $college

                                ]);
                            }
                        }
                    }
                }
            } else {

                return redirect()->back()->with('successexcel', '<h1><i style="color:red; border-radius: 50%;  font-size: 4rem;" class="fas fa fa-times"></i> </h1><h3>Error!</h3> Number of columns does not match');
            }
        }

        $html = '<p style="color: red;">The grants associated with the following LPIs failed to upload, kindly add the followingn users to the system and try again.</p>';

        if (count($arr) > 0) {
            if (!empty($arr)) {
                $html .= '<ul style="color: red;">';
                foreach ($arr as $email) {
                    $html .= '<li>' . htmlspecialchars($email) . '</li>';
                }
                $html .= '</ul>';
            } else {
                $html = '';
            }
        } else {
            $html = '';
        }


        return redirect()->back()->with('successcycle', '<div class="alert alert-success" role="alert">New cycle has been added successfully <br>' . $html . '</div>');
    }

    //use for post
    public function newAnnouncement(Request $request)
    {


        $validator = Validator::make($request->all(), [

            'subject' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string', 'max:500'],
            'duedate' => ['required'],
            'image' => ['required', 'file', 'mimes:png,jpg,jpeg']
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
            ;
        } else {

            $imageUrl = '';
            if ($request->hasFile('image')) {

                $image = $request->file('image');
                $imageName =  time() . '.' . $image->getClientOriginalName();
                $image->move(public_path('uploads'), $imageName);
                $imageUrl = url('uploads/' . $imageName);
            }

            Announcement::create([

                "id" => $request->id,
                "subject" => $request->subject,
                "content" => $request->content,
                "duedate" => $request->duedate,
                "type" => $request->type,
                "image" =>  $imageUrl,

            ]);
            return redirect()->route('announcementSetting');
        }
    }
    public function guageSetting()
    {
        $settings = DB::table('guage_settings')->get();

        return view('guageSetting', ['settings' => $settings]);
    }

    public function guage(Request $request)
    {
        $request->validate([
            'redTo' => 'required',
            'redFrom' => 'required',
            'greenTo' => 'required',
            'greenFrom' => 'required',
            'yellowTo' => 'required',
            'yellowFrom' => 'required'

        ]);

        $record = GuageSettings::find($request->input('id'));

        $record->redfrom = $request->input('redFrom');
        $record->redto = $request->input('redTo');
        $record->greenfrom = $request->input('greenFrom');
        $record->greento = $request->input('greenTo');
        $record->yellowfrom = $request->input('yellowFrom');
        $record->yellowto = $request->input('yellowTo');
        $record->update();

        return redirect()->back()->with('successguage' . $request->input('id'), 'Settings updated successfully');
    }
}
