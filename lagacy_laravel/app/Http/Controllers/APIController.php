<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
//require_once 'vendor\autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\ProjectAPI;
use App\Models\FromConfTool;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use DataTables;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;

class APIController extends Controller
{
    public function __construct()
    {
    }

    public function budgetAPIList()
    {
        $data['date'] = null;
        $aa = DB::table('project_api')->select('*')
            ->get();

        if (count($aa) > 0) {

                $data['date'] =  $aa[0]->updated_at;
        }

        return view('budgetAPIList', $data);
    }

    public function ajaxBudgetAPIList()
    {

        $data = DB::table('project_api')->select('*')
            ->get();

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                $url = route('sendBudgetReminder', ['project_id' => $row->project_name]);
                $Btn = '<a href="' . $url . '" class="btn btn-teal btn-sm">Send Reminder</a>';
                return   $Btn;
            })
            ->make(true);
    }

    /**
     * The function `budgetAPISync` retrieves project data from an API, fetches budget information for
     * each project, and updates the database with the budget details.
     *
     * @param Request request The `budgetAPISync` function you provided seems to be responsible for
     * syncing budget data from an API to your application. Here's a breakdown of what the function
     * does:
     *
     * @return The `budgetAPISync` function is returning a redirect back to the previous page with a
     * success message indicating that the budget data has been fetched successfully from the API. The
     * message is stored in the session flash data with the key 'SyncApi'.
     */

    public function StudentAPI(Request $request)
    {

        $client = new Client();
        $stdid =   $request->input('id');
        $response = $client->request('GET', 'http://quapxweb1.qu.edu.qa/sisapx/qusis/student_info/std', [
            'headers' => [
                'sec_key' => 'STD@R',
                'st_id' => $stdid, //'202000131',
            ]
        ]);


        return json_decode($response->getBody(), true);

        // $str = '
        // {
        //     "items": [
        //         {
        //             "student_id": "202000131",
        //             "first_name": "Hira",
        //             "last_name": "Naseem",
        //             "student_status": "Graduated",
        //             "major": "Computing",
        //             "minor": "Undeclared",
        //             "college": "Engineering",
        //             "std_program": "Master of Science",
        //             "std_level": "Master",
        //             "admission_term": "202010",
        //             "reg_in_course": "Not Registered"
        //         },

        //         {
        //             "student_id": "202000131",
        //             "first_name": "Bahar",
        //             "last_name": "Naseem",
        //             "student_status": "Graduated",
        //             "major": "Computing",
        //             "minor": "Undeclared",
        //             "college": "Engineering",
        //             "std_program": "Master of Science",
        //             "std_level": "Master",
        //             "admission_term": "202010",
        //             "reg_in_course": "Not Registered"
        //         }
        //     ],
        //     "hasMore": false,
        //     "limit": 25,
        //     "offset": 0,
        //     "count": 1,
        //     "links": [
        //         {
        //             "rel": "self",
        //             "href": "http://quapxweb1.qu.edu.qa/sisapx/qusis/student_info/std"
        //         },
        //         {
        //             "rel": "describedby",
        //             "href": "http://quapxweb1.qu.edu.qa/sisapx/qusis/metadata-catalog/student_info/item"
        //         },
        //         {
        //             "rel": "first",
        //             "href": "http://quapxweb1.qu.edu.qa/sisapx/qusis/student_info/std"
        //         }
        //     ]
        // }
        // ';

        // return json_decode($str, true);
    }

    public function budgetAPISync(Request $request)
    {
        //get list from API
        $jsonString = '{"items":[{"project_num":"1007548","project_name":"QUST-1-CPH-2020-25"},{"project_num":"1005974","project_name":"QUST-1-CAS-2018-31"},{"project_num":"1007443","project_name":"QUST-1-CPH-2020-9"},{"project_num":"1006406","project_name":"QUST-2-CMED-2018-3"},{"project_num":"1005969","project_name":"QUST-1-CAS-2018-26"},{"project_num":"1006647","project_name":"QUST-1-CPH-2019-4"},{"project_num":"1005380","project_name":"QUST-CPH-SPR-2017-5"},{"project_num":"1003689","project_name":"QUST-CPH-SPR-13/14-16"},{"project_num":"1006357","project_name":"QUST-2-CPH-2018-8"},{"project_num":"1005384","project_name":"QUST-CPH-SPR-2017-9"},{"project_num":"1005944","project_name":"QUST-1-CPH-2018-13"},{"project_num":"1005934","project_name":"QUST-1-CPH-2018-3"},{"project_num":"1005385","project_name":"QUST-CPH-SPR-2017-10"},{"project_num":"1003494","project_name":"QUST-CPH-FALL-13/14-9"},{"project_num":"1003692","project_name":"QUST-CPH-SPR-13/14-19"},{"project_num":"1003537","project_name":"QUST-CPH-FALL-13/14-14"},{"project_num":"1005662","project_name":"QUST-2-CPH-2017-10"},{"project_num":"1005381","project_name":"QUST-CPH-SPR-2017-6"},{"project_num":"1007078","project_name":"QUST-2-CPH-2019-3"},{"project_num":"1007394","project_name":"QUST-1-CMED-2020-16"},{"project_num":"1007038","project_name":"QUST-2-CAM-2019-3"},{"project_num":"1007049","project_name":"QUST-2-CAS-2019-5"},{"project_num":"1007061","project_name":"QUST-2-CENG-2019-10"},{"project_num":"1007122","project_name":"QUST-2-CHS-2019-5"},{"project_num":"1007135","project_name":"QUST-2-CBE-2019-7"}]}';

        try {
            $response = Http::get('https://residence.qu.edu.qa/ords/qucust/quapi/getProjects/123$$321');
            if ($response->successful()) {
                $jsonString  = $response;
            }
        } catch (\Exception $e) {
            $jsonString = '{"items":[{"project_num":"1007548","project_name":"QUST-1-CPH-2020-25"},{"project_num":"1005974","project_name":"QUST-1-CAS-2018-31"},{"project_num":"1007443","project_name":"QUST-1-CPH-2020-9"},{"project_num":"1006406","project_name":"QUST-2-CMED-2018-3"},{"project_num":"1005969","project_name":"QUST-1-CAS-2018-26"},{"project_num":"1006647","project_name":"QUST-1-CPH-2019-4"},{"project_num":"1005380","project_name":"QUST-CPH-SPR-2017-5"},{"project_num":"1003689","project_name":"QUST-CPH-SPR-13/14-16"},{"project_num":"1006357","project_name":"QUST-2-CPH-2018-8"},{"project_num":"1005384","project_name":"QUST-CPH-SPR-2017-9"},{"project_num":"1005944","project_name":"QUST-1-CPH-2018-13"},{"project_num":"1005934","project_name":"QUST-1-CPH-2018-3"},{"project_num":"1005385","project_name":"QUST-CPH-SPR-2017-10"},{"project_num":"1003494","project_name":"QUST-CPH-FALL-13/14-9"},{"project_num":"1003692","project_name":"QUST-CPH-SPR-13/14-19"},{"project_num":"1003537","project_name":"QUST-CPH-FALL-13/14-14"},{"project_num":"1005662","project_name":"QUST-2-CPH-2017-10"},{"project_num":"1005381","project_name":"QUST-CPH-SPR-2017-6"},{"project_num":"1007078","project_name":"QUST-2-CPH-2019-3"},{"project_num":"1007394","project_name":"QUST-1-CMED-2020-16"},{"project_num":"1007038","project_name":"QUST-2-CAM-2019-3"},{"project_num":"1007049","project_name":"QUST-2-CAS-2019-5"},{"project_num":"1007061","project_name":"QUST-2-CENG-2019-10"},{"project_num":"1007122","project_name":"QUST-2-CHS-2019-5"},{"project_num":"1007135","project_name":"QUST-2-CBE-2019-7"}]}';
        }

        $data = json_decode($jsonString, true);

        if ($data) {
            foreach ($data['items'] as $value) {

                $project_num = $value['project_num'];
                $budgetdata = '';
                try {
                    $response = Http::get('https://residence.qu.edu.qa/ords/qucust/quapi/getProjectBudget/' . $project_num . '/123$$321');
                    if ($response->successful()) {

                        $budgetdata =  json_decode($response);

                        ProjectAPI::updateorCreate(
                            ["project_num" => $budgetdata->items[0]->project_num],
                            [
                            "project_num" => $budgetdata->items[0]->project_num,
                            "project_name" => $budgetdata->items[0]->project_name,
                            "budget_amount" => $budgetdata->items[0]->budget_amount,
                            "actual_exp_amount" => $budgetdata->items[0]->actual_exp_amount,
                            "committment_amount" => $budgetdata->items[0]->committment_amount,
                            "available_balance" => $budgetdata->items[0]->available_balance,
                            "updated_at" => Carbon::now()
                        ]);
                    }
                } catch (\Exception $e) {


                    $response = '{"items":[{"project_num":"1007135","project_name":"QUST-2-CBE-2019-7","budget_amount":10000,"actual_exp_amount":0,"committment_amount":0,"available_balance":10000}]}';

                    $budgetdata =  json_decode($response);

                    ProjectAPI::updateOrInsert(
                        ["project_num" => $budgetdata->items[0]->project_num],
                        [

                        "project_num" => $budgetdata->items[0]->project_num,
                        "project_name" => $budgetdata->items[0]->project_name,
                        "budget_amount" => $budgetdata->items[0]->budget_amount,
                        "actual_exp_amount" => $budgetdata->items[0]->actual_exp_amount,
                        "committment_amount" => $budgetdata->items[0]->committment_amount,
                        "available_balance" => $budgetdata->items[0]->available_balance,
                        "updated_at" => Carbon::now()
                    ]);
                }
            }
        }

        return redirect()->back()->with('SyncApi', 'Budget data fetched successfully');
    }
}
