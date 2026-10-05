<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;

class ProgressReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Save progress report data from modal form
     */
    public function save(Request $request, $project_id)
    {
        $project = DB::table('projects')->find($project_id);
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $data = [
            'project_id' => $project_id,
            'user_id' => Auth::id(),
            'grant_id' => $request->input('grant_id'),
            'lpi_name' => $request->input('lpi_name'),
            'collaborator_institute' => $request->input('collaborator_institute'),
            'collaborator_lpi_name' => $request->input('collaborator_lpi_name'),
            'report_period_from' => $request->input('report_period_from'),
            'report_period_to' => $request->input('report_period_to'),

            // Grant Information
            'funding_duration' => $request->input('funding_duration'),
            'current_year' => $request->input('current_year'),

            // Budget Year 1
            'year1_qu_awarded' => $request->input('year1_qu_awarded', 0),
            'year1_collab_awarded' => $request->input('year1_collab_awarded', 0),
            'year1_qu_actual' => $request->input('year1_qu_actual', 0),
            'year1_collab_actual' => $request->input('year1_collab_actual', 0),

            // Budget Year 2
            'year2_qu_awarded' => $request->input('year2_qu_awarded', 0),
            'year2_collab_awarded' => $request->input('year2_collab_awarded', 0),
            'year2_qu_actual' => $request->input('year2_qu_actual', 0),
            'year2_collab_actual' => $request->input('year2_collab_actual', 0),

            // Budget Year 3
            'year3_qu_awarded' => $request->input('year3_qu_awarded', 0),
            'year3_collab_awarded' => $request->input('year3_collab_awarded', 0),
            'year3_qu_actual' => $request->input('year3_qu_actual', 0),
            'year3_collab_actual' => $request->input('year3_collab_actual', 0),

            // Specific Aims
            'specific_aims' => json_encode($request->input('specific_aims', [])),

            // Results
            'results_achieved' => $request->input('results_achieved'),
            'remaining_questions' => $request->input('remaining_questions'),

            // Grant Outputs
            'committed_outcomes' => json_encode($request->input('committed_outcomes', [])),

            // Publications
            'publications_list' => json_encode($request->input('publications_list', [])),

            // Capacity Building
            'capacity_building_ras' => json_encode($request->input('capacity_building_ras', [])),
            'capacity_building_students' => json_encode($request->input('capacity_building_students', [])),

            // Action Plan
            'action_plan' => json_encode($request->input('action_plan', [])),

            // Other
            'ethical_requirements' => $request->input('ethical_requirements'),
            'potential_difficulties' => $request->input('potential_difficulties'),
            'collaborator_contribution' => $request->input('collaborator_contribution'),
            'appendix' => $request->input('appendix'),
        ];

        // Upsert: update if exists, insert if not
        $existing = DB::table('progress_reports')
            ->where('project_id', $project_id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            DB::table('progress_reports')
                ->where('id', $existing->id)
                ->update($data);
        } else {
            $data['created_at'] = now();
            $data['updated_at'] = now();
            DB::table('progress_reports')->insert($data);
        }

        return response()->json(['success' => true, 'message' => 'Progress report saved successfully.']);
    }

    /**
     * Get progress report data for a project
     */
    public function get($project_id)
    {
        $report = DB::table('progress_reports')
            ->where('project_id', $project_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$report) {
            return response()->json(['exists' => false, 'data' => null]);
        }

        // Decode JSON fields
        $report->specific_aims = json_decode($report->specific_aims, true) ?? [];
        $report->committed_outcomes = json_decode($report->committed_outcomes, true) ?? [];
        $report->publications_list = json_decode($report->publications_list, true) ?? [];
        $report->capacity_building_ras = json_decode($report->capacity_building_ras, true) ?? [];
        $report->capacity_building_students = json_decode($report->capacity_building_students, true) ?? [];
        $report->action_plan = json_decode($report->action_plan, true) ?? [];

        return response()->json(['exists' => true, 'data' => $report]);
    }

    /**
     * Generate DOCX from template and return as HTML view for printing
     */
    public function preview($project_id)
    {
        $report = DB::table('progress_reports')
            ->where('project_id', $project_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$report) {
            return response()->json(['error' => 'No progress report found for this project.'], 404);
        }

        $project = DB::table('projects')->find($project_id);

        // Decode JSON
        $specific_aims = json_decode($report->specific_aims, true) ?? [];
        $committed_outcomes = json_decode($report->committed_outcomes, true) ?? [];
        $publications_list = json_decode($report->publications_list, true) ?? [];
        $capacity_ras = json_decode($report->capacity_building_ras, true) ?? [];
        $capacity_students = json_decode($report->capacity_building_students, true) ?? [];
        $action_plan = json_decode($report->action_plan, true) ?? [];

        return view('studentsGrants.progressReportPreview', compact(
            'report', 'project', 'specific_aims', 'committed_outcomes',
            'publications_list', 'capacity_ras', 'capacity_students', 'action_plan'
        ));
    }

    /**
     * Show the progress report edit form
     */
    public function editForm($project_id)
    {
        $project = DB::table('projects')->find($project_id);
        if (!$project) {
            return redirect()->back()->with('error', 'Project not found.');
        }

        $user = auth()->user();

        // Load existing report if any
        $report = DB::table('progress_reports')
            ->where('project_id', $project_id)
            ->where('user_id', Auth::id())
            ->first();

        return view('studentsGrants.progressReportForm', compact('project', 'user', 'report'));
    }

    /**
     * Generate and download DOCX file
     */
    public function download($project_id)
    {
        $report = DB::table('progress_reports')
            ->where('project_id', $project_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$report) {
            return redirect()->back()->with('error', 'No progress report found.');
        }

        $project = DB::table('projects')->find($project_id);

        // Decode JSON
        $specific_aims = json_decode($report->specific_aims, true) ?? [];
        $committed_outcomes = json_decode($report->committed_outcomes, true) ?? [];
        $publications_list = json_decode($report->publications_list, true) ?? [];
        $capacity_ras = json_decode($report->capacity_building_ras, true) ?? [];
        $capacity_students = json_decode($report->capacity_building_students, true) ?? [];
        $action_plan = json_decode($report->action_plan, true) ?? [];

        // Load template
        $templatePath = storage_path('app/templates/Progress Report template.docx');
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Template file not found.');
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // Set simple values
        $templateProcessor->setValue('grant_id', $report->grant_id ?? 'N/A');
        $templateProcessor->setValue('lpi_name', $report->lpi_name ?? 'N/A');
        $templateProcessor->setValue('collaborator_institute', $report->collaborator_institute ?? 'N/A');
        $templateProcessor->setValue('collaborator_lpi_name', $report->collaborator_lpi_name ?? 'N/A');
        $templateProcessor->setValue('report_period', ($report->report_period_from ?? 'dd/mm/yyyy') . ' until ' . ($report->report_period_to ?? 'dd/mm/yyyy'));
        $templateProcessor->setValue('project_title', $project->title ?? 'N/A');

        // Grant Information
        $templateProcessor->setValue('grant_id_info', $report->grant_id ?? 'N/A');
        $templateProcessor->setValue('funding_duration', $report->funding_duration ?? 'N/A');
        $templateProcessor->setValue('current_year', $report->current_year ?? 'N/A');

        // Budget rows - we'll clone rows
        // For simplicity, set year-based values
        $templateProcessor->setValue('year1_qu_awarded', number_format($report->year1_qu_awarded, 2));
        $templateProcessor->setValue('year1_collab_awarded', number_format($report->year1_collab_awarded, 2));
        $templateProcessor->setValue('year1_qu_actual', number_format($report->year1_qu_actual, 2));
        $templateProcessor->setValue('year1_collab_actual', number_format($report->year1_collab_actual, 2));

        $templateProcessor->setValue('year2_qu_awarded', number_format($report->year2_qu_awarded, 2));
        $templateProcessor->setValue('year2_collab_awarded', number_format($report->year2_collab_awarded, 2));
        $templateProcessor->setValue('year2_qu_actual', number_format($report->year2_qu_actual, 2));
        $templateProcessor->setValue('year2_collab_actual', number_format($report->year2_collab_actual, 2));

        $templateProcessor->setValue('year3_qu_awarded', number_format($report->year3_qu_awarded, 2));
        $templateProcessor->setValue('year3_collab_awarded', number_format($report->year3_collab_awarded, 2));
        $templateProcessor->setValue('year3_qu_actual', number_format($report->year3_qu_actual, 2));
        $templateProcessor->setValue('year3_collab_actual', number_format($report->year3_collab_actual, 2));

        // Results
        $templateProcessor->setValue('results_achieved', $report->results_achieved ?? 'N/A');
        $templateProcessor->setValue('remaining_questions', $report->remaining_questions ?? 'N/A');

        // Publications
        $pubText = '';
        foreach ($publications_list as $i => $pub) {
            if (!empty($pub['text'])) {
                $pubText .= ($i + 1) . '. ' . $pub['text'] . "\n";
            }
        }
        $templateProcessor->setValue('publications_text', $pubText ?: 'N/A');

        // Capacity Building
        $raText = '';
        foreach ($capacity_ras as $i => $ra) {
            if (!empty($ra['name'])) {
                $raText .= ($i + 1) . '. ' . $ra['name'] . ' - ' . ($ra['details'] ?? '') . "\n";
            }
        }
        $templateProcessor->setValue('capacity_ras', $raText ?: 'N/A');

        $studentText = '';
        foreach ($capacity_students as $i => $s) {
            if (!empty($s['name'])) {
                $studentText .= ($i + 1) . '. ' . $s['name'] . ' - ' . ($s['details'] ?? '') . "\n";
            }
        }
        $templateProcessor->setValue('capacity_students', $studentText ?: 'N/A');

        // Action Plan
        $actionText = '';
        foreach ($action_plan as $i => $ap) {
            if (!empty($ap['aim']) || !empty($ap['plan'])) {
                $actionText .= "Aim (" . ($i + 1) . "): " . ($ap['aim'] ?? '') . "\n";
                $actionText .= "Action Plan: " . ($ap['plan'] ?? '') . "\n\n";
            }
        }
        $templateProcessor->setValue('action_plan_text', $actionText ?: 'N/A');

        // Ethical
        $templateProcessor->setValue('ethical_requirements', $report->ethical_requirements ?? 'N/A');

        // Difficulties
        $templateProcessor->setValue('potential_difficulties', $report->potential_difficulties ?? 'N/A');

        // Collaborator
        $templateProcessor->setValue('collaborator_contribution', $report->collaborator_contribution ?? 'N/A');

        // Appendix
        $templateProcessor->setValue('appendix', $report->appendix ?? 'N/A');

        // Save temp file
        $fileName = 'Progress_Report_' . $project->old_project_id . '_' . time() . '.docx';
        $tempPath = storage_path('app/temp/' . $fileName);
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        $templateProcessor->saveAs($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
