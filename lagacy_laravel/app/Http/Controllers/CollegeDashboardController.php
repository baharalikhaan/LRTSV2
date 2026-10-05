<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollegeDashboardController extends Controller
{

    public function fetchDataPillar(Request $request)
    {
        try {
            $parameter = $request->query('parameter', 'all');

            if ($parameter !== 'all') {
                // Commitments query with a single parameter
                $commitmentsQuery = DB::table('commitments_pillar_wise')
                    ->select('pillar', 'subpillar', DB::raw("SUM($parameter) AS q1"))
                    ->groupBy('pillar', 'subpillar')
                    ->get();
            } else {
                // Commitments query for all parameters
                $commitmentsQuery = DB::table('commitments_pillar_wise')
                    ->select(
                        'pillar',
                        'subpillar',
                        DB::raw("SUM(q1) AS q1"),
                        DB::raw("SUM(q2) AS q2"),
                        DB::raw("SUM(q3) AS q3"),
                        DB::raw("SUM(q4) AS q4"),
                        DB::raw("SUM(conference) AS conference"),
                        DB::raw("SUM(BookPublish) AS BookPublish"),
                        DB::raw("SUM(EditBook) AS EditBook"),
                        DB::raw("SUM(BookChapter) AS BookChapter"),
                        DB::raw("SUM(IP) AS IP"),
                        DB::raw("SUM(GrantedPatents) AS GrantedPatents"),
                        DB::raw("SUM(OpenSW) AS OpenSW"),
                        DB::raw("SUM(SUp) AS SUp"),
                        DB::raw("SUM(master) AS master"),
                        DB::raw("SUM(UG) AS UG"),
                        DB::raw("SUM(PhD) AS PhD")
                    )
                    ->groupBy('pillar', 'subpillar')
                    ->get();
            }

            if ($parameter !== 'all') {
                // Outcomes query with a single parameter
                $outcomesQuery = DB::table('outcomes_pillar_wise')
                    ->select('pillar', 'subpillar', DB::raw("SUM($parameter) AS q1"))
                    ->groupBy('pillar', 'subpillar')
                    ->get();
            } else {
                // Outcomes query for all parameters
                $outcomesQuery = DB::table('outcomes_pillar_wise')
                    ->select(
                        'pillar',
                        'subpillar',
                        DB::raw("SUM(q1) AS q1"),
                        DB::raw("SUM(q2) AS q2"),
                        DB::raw("SUM(q3) AS q3"),
                        DB::raw("SUM(q4) AS q4"),
                        DB::raw("SUM(conference) AS conference"),
                        DB::raw("SUM(BookPublish) AS BookPublish"),
                        DB::raw("SUM(EditBook) AS EditBook"),
                        DB::raw("SUM(BookChapter) AS BookChapter"),
                        DB::raw("SUM(IP) AS IP"),
                        DB::raw("SUM(GrantedPatents) AS GrantedPatents"),
                        DB::raw("SUM(OpenSW) AS OpenSW"),
                        DB::raw("SUM(SUp) AS SUp"),
                        DB::raw("SUM(master) AS master"),
                        DB::raw("SUM(UG) AS UG"),
                        DB::raw("SUM(PhD) AS PhD")
                    )
                    ->groupBy('pillar', 'subpillar')
                    ->get();
            }

            // Merge commitments and outcomes
            $data = [];
            foreach ($commitmentsQuery as $commitment) {
                $key = $commitment->pillar . '-' . $commitment->subpillar;
                $data[$key] = [
                    'pillar' => $commitment->pillar,
                    'subpillar' => $commitment->subpillar,
                    'commitments' => $commitment,
                    'outcomes' => null,
                ];
            }

            foreach ($outcomesQuery as $outcome) {
                $key = $outcome->pillar . '-' . $outcome->subpillar;
                if (isset($data[$key])) {
                    $data[$key]['outcomes'] = $outcome;
                } else {
                    $data[$key] = [
                        'pillar' => $outcome->pillar,
                        'subpillar' => $outcome->subpillar,
                        'commitments' => null,
                        'outcomes' => $outcome,
                    ];
                }
            }

            return response()->json(array_values($data));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fetchDataCollege(Request $request)
    {
        try {
            $parameter = $request->query('parameter', 'all');

            if ($parameter !== 'all') {
                // Commitments query with a single parameter
                $commitmentsQuery = DB::table('commitments_tag_wise')
                    ->select('tag', 'tagtitle', DB::raw("SUM($parameter) AS q1"))
                    ->groupBy('tag', 'tagtitle')
                    ->get();
            } else {
                // Commitments query for all parameters
                $commitmentsQuery = DB::table('commitments_tag_wise')
                    ->select(
                        'tag',
                        'tagtitle',
                        DB::raw("SUM(q1) AS q1"),
                        DB::raw("SUM(q2) AS q2"),
                        DB::raw("SUM(q3) AS q3"),
                        DB::raw("SUM(q4) AS q4"),
                        DB::raw("SUM(conference) AS conference"),
                        DB::raw("SUM(BookPublish) AS BookPublish"),
                        DB::raw("SUM(EditBook) AS EditBook"),
                        DB::raw("SUM(BookChapter) AS BookChapter"),
                        DB::raw("SUM(IP) AS IP"),
                        DB::raw("SUM(GrantedPatents) AS GrantedPatents"),
                        DB::raw("SUM(OpenSW) AS OpenSW"),
                        DB::raw("SUM(SUp) AS SUp"),
                        DB::raw("SUM(master) AS master"),
                        DB::raw("SUM(UG) AS UG"),
                        DB::raw("SUM(PhD) AS PhD")
                    )
                    ->groupBy('tag', 'tagtitle')
                    ->get();
            }

            if ($parameter !== 'all') {
                // Outcomes query with a single parameter
                $outcomesQuery = DB::table('outcomes_tag_wise')
                    ->select('tag', 'tagtitle', DB::raw("SUM($parameter) AS q1"))
                    ->groupBy('tag', 'tagtitle')
                    ->get();
            } else {
                // Outcomes query for all parameters
                $outcomesQuery = DB::table('outcomes_tag_wise')
                    ->select(
                        'tag',
                        'tagtitle',
                        DB::raw("SUM(q1) AS q1"),
                        DB::raw("SUM(q2) AS q2"),
                        DB::raw("SUM(q3) AS q3"),
                        DB::raw("SUM(q4) AS q4"),
                        DB::raw("SUM(conference) AS conference"),
                        DB::raw("SUM(BookPublish) AS BookPublish"),
                        DB::raw("SUM(EditBook) AS EditBook"),
                        DB::raw("SUM(BookChapter) AS BookChapter"),
                        DB::raw("SUM(IP) AS IP"),
                        DB::raw("SUM(GrantedPatents) AS GrantedPatents"),
                        DB::raw("SUM(OpenSW) AS OpenSW"),
                        DB::raw("SUM(SUp) AS SUp"),
                        DB::raw("SUM(master) AS master"),
                        DB::raw("SUM(UG) AS UG"),
                        DB::raw("SUM(PhD) AS PhD")
                    )
                    ->groupBy('tag', 'tagtitle')
                    ->get();
            }

            // Merge commitments and outcomes
            $data = [];
            foreach ($commitmentsQuery as $commitment) {
                $key = $commitment->tag . '-' . $commitment->tagtitle;
                $data[$key] = [
                    'tag' => $commitment->tag,
                    'tagtitle' => $commitment->tagtitle,
                    'commitments' => $commitment,
                    'outcomes' => null,
                ];
            }

            foreach ($outcomesQuery as $outcome) {
                $key = $outcome->tag . '-' . $outcome->tagtitle;
                if (isset($data[$key])) {
                    $data[$key]['outcomes'] = $outcome;
                } else {
                    $data[$key] = [
                        'tag' => $outcome->tag,
                        'tagtitle' => $outcome->tagtitle,
                        'commitments' => null,
                        'outcomes' => $outcome,
                    ];
                }
            }

            return response()->json(array_values($data));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function showDashboard()
    {
        return view('CollegeDashboard/CollegeDashboard');
    }
}
