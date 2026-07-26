<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    /**
     * Show the "About RTS" page — application info, version, features, version history.
     */
    public function index()
    {
        $versionHistory = [
            [
                'version'   => '2.0.0',
                'date'      => 'July 2026',
                'tagline'   => 'Fluent Design Refresh & Workflow Overhaul',
                'changes'   => [
                    'Complete UI redesign — QU × Fluent Design System with custom token set',
                    'Acrylic command bar & sidebar with mica-textured brand surfaces',
                    'Role-based dashboards: Admin, LPI, and Reviewer views',
                    'Workflow engine — proposal accept/reject, grading, progress tracking',
                    'Report Card modal with structured evaluation summary',
                    'Built-in DataTables with CSV/Excel/PDF/Print export across all tables',
                    'Reviewer assignment system with mutual-exclusion logic',
                    'Outcomes management via registration wizard',
                    'LPI Contribution Summary dashboard gadgets (grants, cycles, programs, publications, students)',
                ],
            ],
            [
                'version'   => '1.0.0',
                'date'      => 'June 2026',
                'tagline'   => 'Initial Release',
                'changes'   => [
                    'Core project CRUD with status tracking',
                    'User management with role-based access control',
                    'Cycle & program configuration framework',
                    'Project registration wizard (multi-step form)',
                    'Basic reporting: program status, grant summary, project status',
                    'Basic dashboard with project counts',
                    'Authentication & authorization scaffolding',
                ],
            ],
        ];

        return view('about.index', compact('versionHistory'));
    }

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
}
