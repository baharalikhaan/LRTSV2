<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\ProjectPillar;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{




    public function load()
    {

        // 1. Projects by Grant Type
        $grantTypeData = DB::table('projects as p')
            ->join('cycle as c', 'c.id', '=', 'p.cycle')
            ->select('c.grant_type', DB::raw('COUNT(DISTINCT p.id) as total'))
            ->groupBy('c.grant_type')
            ->get();

        // 2. Projects per Pillar
        $pillarData = DB::table('projects as p')
            ->join('project_pillar as pp', 'pp.project_id', '=', 'p.id')
            ->join('pillars as pl', 'pl.id', '=', 'pp.pillar_id')
            ->select('pl.pillar', DB::raw('COUNT(DISTINCT p.id) as total'))
            ->groupBy('pl.pillar')
            ->get();

        // 3. Projects per Cycle
        $cycleData = DB::table('projects as p')
            ->join('cycle as c', 'c.id', '=', 'p.cycle')
            ->select('c.cycle_title', DB::raw('COUNT(DISTINCT p.id) as total'))
            ->groupBy('c.cycle_title')
            ->get();

        // 4. Tags vs Project Count
        $tagData = DB::table('projects as p')
            ->join('project_tag as pt', 'pt.project_id', '=', 'p.id')
            ->join('tags as t', 't.id', '=', 'pt.tag_id')
            ->select('t.tag', DB::raw('COUNT(DISTINCT p.id) as total'))
            ->groupBy('t.tag')
            ->get();

        // 5. Registered vs Unregistered Projects
        $registeredData = DB::table('from_conf_tool as t')

            ->select(
                DB::raw('CASE WHEN t.added = 1 THEN "Registered" ELSE "Unregistered" END as status'),
                DB::raw('COUNT(DISTINCT t.id) as total')
            )
            ->groupBy('status')
            ->get();

        return view('dashboard2', compact('grantTypeData', 'pillarData', 'cycleData', 'tagData','registeredData'));
        /********************************************************************************************** */
        $pillar = ProjectPillar::leftJoin('pillars', 'project_pillar.pillar_id', '=', 'pillars.id')
            ->select('pillars.id as pillar_id', 'pillars.pillar as pillar_name', \DB::raw('count(project_pillar.id) as project_count'))
            ->groupBy('pillars.id', 'pillars.pillar')
            ->get();


        //   dd($pillar);
        $cycle = DB::table('cycle')->where('status', '=', 'active')->selectRaw('count(id) as cnt')->pluck('cnt');
        $cyc = DB::table('cycle')->where('status', '=', 'active')
            ->get();
        $users = DB::table('users')->selectRaw('count(id) as cnt')->pluck('cnt');
        $projects = DB::table('projects')->selectRaw('count(id) as cnt')->pluck('cnt');


        $testing = DB::table('test_table')
            ->select('*')
            ->pluck('Id', 'value_1');
        //dd($testing);
        $labels = $testing->values();
        $data = $testing->keys();

        $projectsincycle = Project::leftJoin('cycle', 'cycle.id', '=', 'projects.cycle')
            ->selectRaw('cycle.cycle_title,   count(projects.id) as total')
            ->groupBy('cycle.cycle_title')
            ->get();

        $pillarwise = Project::leftJoin('project_pillar', 'project_pillar.project_id', '=', 'projects.id')
            ->leftJoin('pillars', 'pillars.id', '=', 'project_pillar.pillar_id')
            ->selectRaw('pillars.pillar,   count(projects.id) as total')
            ->groupBy('pillars.pillar')
            ->get();

        $pillarlpi = DB::table('user_pillars')->selectRaw('count(users.id) as total, pillars.pillar')
            ->join('users', 'users.id', '=', 'user_pillars.user_id')
            ->Leftjoin('pillars', 'pillars.id', '=', 'user_pillars.pillar_id')
            ->where('users.type', 'LPI')
            ->orWhere('users.type', 'LPI+Reviewer')
            ->groupBy('pillars.pillar')
            ->get();

        $pillarreviewer = DB::table('user_pillars')->selectRaw('pillars.pillar,count(users.id) as total')
            ->join('users', 'users.id', '=', 'user_pillars.user_id')
            ->Leftjoin('pillars', 'pillars.id', '=', 'user_pillars.pillar_id')
            ->where('users.type', 'Reviewer')
            ->orWhere('users.type', 'LPI+Reviewer')
            ->groupBy('pillars.pillar')
            ->get();

        $collegewise =  Project::leftJoin('project_tag', 'project_tag.project_id', '=', 'projects.id')
            ->LeftJoin('tags', 'tags.id', '=', 'project_tag.tag_id')
            ->selectRaw('tags.tagtitle,   count(projects.id) as total')
            ->groupBy('tags.tagtitle')
            ->get();
        //   dd($collegewise);

        $activeCycles = Cycle::where('status', 'active')->get(['id', 'cycle_title']);


        return view('Dashboard', ['activeCycles' => $activeCycles, 'projectsincycle' => $projectsincycle, 'pillarreviewer' => $pillarreviewer, 'pillarlpi' => $pillarlpi, 'collegewise' => $collegewise, 'pillarwise' =>    $pillarwise, 'pillar' => $pillar, 'cycle' => $cycle, 'users' => $users, 'projects' => $projects, 'labels' => $labels, 'data' => $data, 'cyc' => $cyc]);
    }
}
