<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

use App\Exceptions;
use App\Models\AttachedStudent;
use App\Models\Outcome;
use App\Models\PublicationDetail;
use App\Models\Contribution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Submission_files;
use App\Models\Submissions;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Throwable;

class OutcomesController extends Controller
{


    public function projectOutcomesstudent(Request $request)
    {

        $validatedData = $request->validate([
            'spending' => 'required|numeric|min:0',
            'student_engagement' => 'required|string|min:1',
        ]);


        try {
            DB::beginTransaction();

            //  dd(json_encode($request->publications));


            // Update studentgrant_students table
            if ($request->students) {
                foreach ($request->students as $student) {
                    DB::table('studentgrant_students')
                        ->where('id', $student['id'])
                        ->update([
                            'nationality' => strtolower($student['category']), // Store as lowercase for consistency
                            'updated_at' => now(),
                        ]);
                }
            }

            $pub = explode(',', $request->publications[0]);
            //create outcomes

            if (count($pub) == 8) {

                $outcome = Outcome::create([
                    'project_id' =>  $request->project_id,
                    'type' => '',
                    'identifier' => '',
                    'score' => 0,
                    'online_date' =>  $pub[5] . '-' . $pub[6] . '-' . $pub[7]
                ]);

                PublicationDetail::create([
                    'title' => $pub[1],
                    'publication_date' => $outcome->online_date, // Use correct date format
                    'venue' =>  $pub[2],
                    'type' => '', // or 'Conference', etc.
                    'outcome_id' => $outcome->id, // Make sure this ID exists in related table if foreign key
                    'url' => ''
                ]);
            }

            //insert into publication details




            //   Update projects table
            $updateData = [
                'spending' => $request->spending,
                'spending_detail' => $request->spending_details,
                'requested_budget_qar' => $request->requested_budget_qar,
                'student_engagement' => $request->student_engagement,
                'updated_at' => now(),
                'student_project_draft' => $request->action,
            ];

            // Conditionally add 'publications' if it exists and has a value
            if (isset($request->publications[0]) && !empty($request->publications[0])) {
                $updateData['publications'] = $request->publications[0];
            }
            else
            {
                 $updateData['publications']='';
            }

            DB::table('projects')
                ->where('id', $request->project_id)
                ->update($updateData);


            DB::commit();
            $project = DB::table('projects')
                ->selectRaw('projects.*,cycle.cycle_title,cycle.grant_type')
                ->join('cycle', 'cycle.id', '=', 'projects.cycle')
                ->where('projects.id', '=', $request->project_id)
                ->first();
            $count = 0;
            if ($request->hasFile('ethical_approval')) {
                //  dd($request->file('ethical_approval'));

                // foreach ($request->file('ethical_approval') as $file) {
                //     $count++;
                //     // $fileName = time() . '_' . $file->getClientOriginalName();
                //     $file->storeAs('uploads/ethical_approvals/' . $project->cycle_title . '/' . $project->old_project_id . '/', $project->old_project_id . "_" . $count . ".pdf");

                //     //     $file->storeAs('uploads/ethical_approvals/', $fileName); // Save to storage
                //     //    $path = Storage::putFileAs('uploads/ethical_approvals/' . $project->cycle_title . '/', $request->file('file'), $project->old_project_id . ".pdf");
                // }



                $directory = 'uploads/ethical_approvals/' . $project->cycle_title . '/' . $project->old_project_id . '/';

                // Get all files in the directory
                $existingFiles = Storage::files($directory);

                // Extract used numbers
                $usedNumbers = [];
                foreach ($existingFiles as $file) {
                    // Extract filename like "123_1.pdf" and get the number part
                    if (preg_match('/_(\d+)\.pdf$/', $file, $matches)) {
                        $usedNumbers[] = (int)$matches[1];
                    }
                }

                sort($usedNumbers);
                $nextNumber = 1;

                // Function to find the first missing number
                function getNextAvailableNumber($usedNumbers)
                {
                    $i = 1;
                    foreach ($usedNumbers as $num) {
                        if ($num != $i) {
                            return $i;
                        }
                        $i++;
                    }
                    return $i;
                }

                // Now store the uploaded files
                foreach ($request->file('ethical_approval') as $file) {
                    $nextNumber = getNextAvailableNumber($usedNumbers);
                    $fileName = $project->old_project_id . "_" . $nextNumber . ".pdf";
                    $file->storeAs($directory, $fileName);

                    $usedNumbers[] = $nextNumber;
                    sort($usedNumbers); // re-sort in case the array got updated out of order
                }
            }

            return back()->with('success', 'Files uploaded successfully.');


            //    return response()->json(['message' => 'Records updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }


    public function deletefile(Request $request)
    {
        $fileName = $request->file;

        // Define the file path
        //    $filePath = storage_path('app/ethical_approvals/' . $fileName);

        $filePath = 'uploads/ethical_approvals/' . $fileName; // Relative to storage/app/
        //  dd($filePath);
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
            echo "1";
        } else {
            echo "0";
        }
    }


    public function projectOutcomes(Request $request)
    {
        // dd($request->p_id);
        // $recordExists = Project::where('id', $request->p_id)->where('user_id', Auth::user()->id)->first();

        // if (!$recordExists) {
        //     return "Un-authorized";
        // }

        return view('outcomeStep1', ['p_id' => $request->p_id]);
    }
    public function projectOutcome(REQUEST $request)
    {
        //  dd($request);
        $request->validate([
            'IP' => 'required',
            'FP' => 'required',
            'GP' => 'required',
            'SW' => 'required',
            'SUp' => 'required',

        ]);
        $scholarly = $request->all();
        $request->session()->put('scholarly', $scholarly);
        dd($scholarly);
        return view('outcomeStep1');
        // return redirect('outcomeStep2');
    }
    public function projectOutcome2(REQUEST $request)
    {
        //dd($request);
        $scholarly = $request->all();
        $request->session()->put('scholarly', $scholarly);
        return view('outcomeStep2');
    }
    public function projectOutcome3(REQUEST $request)
    {

        //     dd($request);
        $contribution = $request->all();
        $request->session()->put('contribution', $contribution);
        //dd($request, $contribution, session()->get('scholarly'));
        $test = session()->get('scholarly');
        $pid =  $test['p_id'];
        //    dd($test);
        //dd($test["IP"]);
        DB::transaction(function () use ($request) {
            $scholarly = session()->get('scholarly');

            $contribution = session()->get('contribution');
            $user_id = auth()->user()->id;
            $p_id = $scholarly['p_id'];

            //    dd($contribution);
            if (!empty($scholarly['q1'])) {
                foreach ($scholarly['q1'] as $q1) {
                    $outcome = Outcome::create([
                        "project_id" => $p_id,
                        "user_id" => $user_id,
                        "type" => 'q1',
                        "identifier" => $q1,
                        "score" => '8',
                    ]);
                    OutcomesController::API($outcome->identifier, $outcome->id);
                    //code here
                }
            }

            if (!empty($scholarly['q2'])) {
                foreach ($scholarly['q2'] as $q2) {
                    $outcome = Outcome::create([
                        "project_id" => $p_id,
                        "user_id" => $user_id,
                        "type" => 'q2',
                        "identifier" => $q2,
                        "score" => '6',
                    ]);
                    OutcomesController::API($outcome->identifier, $outcome->id);
                }
            }
            if (!empty($scholarly['q3'])) {
                foreach ($scholarly['q3'] as $q3) {
                    $outcome = Outcome::create([
                        "project_id" => $p_id,
                        "user_id" => $user_id,
                        "type" => 'q3',
                        "identifier" => $q3,
                        "score" => '4',
                    ]);
                    OutcomesController::API($outcome->identifier, $outcome->id);
                }
            }

            if (!empty($scholarly['q4'])) {
                foreach ($scholarly['q4'] as $q4) {
                    $outcome = Outcome::create([
                        "project_id" => $p_id,
                        "user_id" => $user_id,
                        "type" => 'q4',
                        "identifier" => $q4,
                        "score" => '3',
                    ]);
                    OutcomesController::API($outcome->identifier, $outcome->id);
                }
            }

            if (!empty($scholarly['conf'])) {
                foreach ($scholarly['conf'] as $conf) {
                    $outcome = Outcome::create([
                        "project_id" => $p_id,
                        "user_id" => $user_id,
                        "type" => 'conf',
                        "identifier" => $conf,
                        "score" => '2',
                    ]);
                    OutcomesController::API($outcome->identifier, $outcome->id);
                }
            }

            if (!empty($scholarly['pubBook'])) {
                foreach ($scholarly['pubBook'] as $pubBook) {
                    $outcome = Outcome::create([
                        "project_id" => $p_id,
                        "user_id" => $user_id,
                        "type" => 'pubBook',
                        "identifier" => $pubBook,
                        "score" => '8',
                    ]);
                    OutcomesController::API($outcome->identifier, $outcome->id);
                }
            }

            if (!empty($scholarly['editBook'])) {
                foreach ($scholarly['editBook'] as $editBook) {
                    $outcome = Outcome::create([
                        "project_id" => $p_id,
                        "user_id" => $user_id,
                        "type" => 'editBook',
                        "identifier" => $editBook,
                        "score" => '6',
                    ]);
                    OutcomesController::API($outcome->identifier, $outcome->id);
                }
            }

            if (!empty($scholarly['bookChap'])) {
                foreach ($scholarly['bookChap'] as $bookChap) {
                    $outcome = Outcome::create([
                        "project_id" => $p_id,
                        "user_id" => $user_id,
                        "type" => 'bookChap',
                        "identifier" => $bookChap,
                        "score" => '4',
                    ]);
                    OutcomesController::API($outcome->identifier, $outcome->id);
                }
            }

            if (!empty($scholarly['IP']) && ($scholarly['IP'] === "1")) {
                Contribution::create([
                    "project_id" => $p_id,
                    "user_id" => $user_id,
                    "type" => 'IP',
                    "detail" => $scholarly['IPText'],
                    "score" => '4'
                ]);
            }
            if (!empty($scholarly['FP']) && ($scholarly['FP'] === "1")) {
                Contribution::create([
                    "project_id" => $p_id,
                    "user_id" => $user_id,
                    "type" => 'ProvisionalPatents',
                    "detail" => $scholarly['FPText'],
                    "score" => '7'
                ]);
            }
            if (!empty($scholarly['GP']) && ($scholarly['GP'] === "1")) {
                Contribution::create([
                    "project_id" => $p_id,
                    "user_id" => $user_id,
                    "type" => 'GrantedPatents',
                    "detail" => $scholarly['GPText'],
                    "score" => '9'
                ]);
            }
            if (!empty($scholarly['SW']) && ($scholarly['SW'] === "1")) {
                Contribution::create([
                    "project_id" => $p_id,
                    "user_id" => $user_id,
                    "type" => 'OpenSW',
                    "detail" => $scholarly['SWText'],
                    "score" => '8'
                ]);
            }
            if (!empty($scholarly['SUp']) && ($scholarly['SUp'] === "1")) {
                Contribution::create([
                    "project_id" => $p_id,
                    "user_id" => $user_id,
                    "type" => 'SUp',
                    "detail" => $scholarly['SUpText'],
                    "score" => '10'
                ]);
            }
            if (!empty($request['masters'])) {
                $counter = 0;
                foreach ($request['masters'] as $masters) {
                    if ($counter % 2 == 0) {
                        AttachedStudent::create([
                            "project_id" => $p_id,
                            "user_id" => $user_id,
                            "type" => 'masters',
                            "std_id" => $request['masters'][$counter + 0],
                            "days" => $request['masters'][$counter + 1],
                            "score" => '2'
                        ]);
                    }
                    $counter++;
                }
            }
            if (!empty($request['UG'])) {
                $counter = 0;
                foreach ($request['UG'] as $UG) {
                    if ($counter % 2 == 0) {
                        AttachedStudent::create([
                            "project_id" => $p_id,
                            "user_id" => $user_id,
                            "type" => 'UG',
                            "std_id" => $request['UG'][$counter + 0],
                            "days" => $request['UG'][$counter + 1],
                            "score" => '1'
                        ]);
                    }
                    $counter++;
                }
            }

            if (!empty($request['PhD'])) {
                $counter = 0;
                foreach ($request['PhD'] as $PhD) {
                    if ($counter % 2 == 0) {
                        AttachedStudent::create([
                            "project_id" => $p_id,
                            "user_id" => $user_id,
                            "type" => 'PhD',
                            "std_id" => $request['PhD'][$counter + 0],
                            "days" => $request['PhD'][$counter + 1],
                            "score" => '3'
                        ]);
                    }
                    $counter++;
                }
            }
        });

        return redirect('upload/' . $pid)->with('successoutcome', '<p style="color: green;"> Outcomes updated successfully</p>');
        //     return view('home', ['message' => 'Detail list of outcomes uploaded successfully!']);


    }
    public function API($doi, $outcome_id)
    {
        try {
            // $doi = "10.1109/ICICT50816.2021.9358703";
            $url = "https://api.crossref.org/works/" . $doi;
            $client = new \GuzzleHttp\Client(['base_uri' => 'https://api.crossref.org/works/', 'verify' => false]);
            $response = $client->request('GET', "$doi");
            //   dd(json_decode($response->getBody()->getContents(), true));
            if (isset($response)) {
                $res = json_decode($response->getBody()->getContents(), true);
                $title = $res['message']['title'][0];
                $venue = $res['message']['publisher'];
                $type = $res['message']['type'];
                $url = $res['message']['URL'];
                // $author = $res['message']['author'][0]['given'] . $res['message']['author'][0]['family'];
                $pubDate = $res['message']['indexed']['date-time'];
                PublicationDetail::create([
                    "outcome_id" => $outcome_id,
                    "title" => $title,
                    "publication_date" => $pubDate,
                    "type" => $type,
                    "venue" => $venue,
                    "url" => $url
                ]);
                // dd('here');
            } else
                dd('not found');
        } catch (Throwable $e) {
            //  dd($e);
            return false;
        }
    }

    public function testing2()
    {
        OutcomesController::API('10.1109/QRS51102.2020.00045', '41');
        //  dd('I am here now');
    }
    public function uHass(Request $request)
    {
        return view('hass', ['p_id' => $request->p_id]);
    }
    public function hassOutcomes(Request $request)
    {
        dd($request);
    }
}
