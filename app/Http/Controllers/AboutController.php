<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{

    /**
     * Show the interactive Help page — role-specific guides.
     */
    public function help()
    {
        return view('about.help');
    }

    /**
     * Show the "Our Team" page — team members from the team table.
     */
    public function team()
    {
        $teamMembers = \App\Models\Team::all();

        return view('about.team', compact('teamMembers'));
    }

    /**
     * Show the LPI user manual.
     */
    public function lpiManual()
    {
        return view('about.lpi-manual');
    }

    /**
     * Show the Reviewer user manual.
     */
    public function reviewerManual()
    {
        return view('about.reviewer-manual');
    }
}
