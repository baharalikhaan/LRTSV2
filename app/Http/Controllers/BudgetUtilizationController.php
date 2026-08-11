<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBudget;
use App\Models\Program;
use App\Models\User;
use App\Models\EmailSendLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\GenericEmailMail;

class BudgetUtilizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || !$user->isAdmin()) {
                return redirect()->route('home')->with('error', 'Unauthorized.');
            }
            return $next($request);
        });
    }

    /**
     * Show budget utilization page.
     */
    public function index()
    {
        $programs = Program::with('grant', 'cycle')
            ->withCount('projects as project_count')
            ->orderByDesc('id')
            ->get();

        $lastSync = ProjectBudget::max('last_synced_at');

        return view('admin.budget-utilization.index', compact('programs', 'lastSync'));
    }

    /**
     * Server-side DataTable AJAX endpoint.
     */
    public function ajaxList(Request $request)
    {
        $search  = $request->input('search.value', '');
        $program = $request->input('program_id', '');

        $query = ProjectBudget::with('project.program.grant', 'project.program.cycle', 'project.lpi')
            ->select('project_budgets.*');

        // Filter by program (research call)
        if ($program) {
            $query->whereHas('project', function ($q) use ($program) {
                $q->where('program_id', $program);
            });
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhere('project_num', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($q2) use ($search) {
                      $q2->where('old_project_id', 'like', "%{$search}%");
                  });
            });
        }

        $total = $query->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $budgets = $query->orderByDesc('project_budgets.id')
            ->skip($start)->take($length)
            ->get();

        $data = $budgets->map(function ($b) {
            $project = $b->project;
            $oldId   = $project->old_project_id ?? $project->id;
            $lpiName = $project->lpi ? $project->lpi->name : '—';
            $pct     = $b->utilizationPercent();
            $status  = $b->utilizationStatus();

            $pctColor = $status === 'warning' ? '#f59e0b' : ($status === 'success' ? '#22c55e' : '#2563eb');

            return [
                'project_num'    => e($oldId),
                'project_name'   => '<div style="font-weight:500;max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="' . e($project->title) . '">' . e($project->title) . '</div>',
                'lpi'            => e($lpiName),
                'budget'         => number_format($b->budget_amount, 2),
                'actual'         => number_format($b->actual_exp_amount, 2),
                'commitment'     => number_format($b->commitment_amount, 2),
                'balance'        => number_format($b->available_balance, 2),
                'utilization'    => '<div style="display:flex;align-items:center;gap:8px;">' .
                    '<div style="flex:1;height:6px;background:#e5e7eb;border-radius:3px;overflow:hidden;min-width:60px;">' .
                    '<div style="height:100%;width:' . min($pct, 100) . '%;background:' . $pctColor . ';border-radius:3px;"></div>' .
                    '</div>' .
                    '<span style="font-size:12px;font-weight:600;color:' . $pctColor . ';">' . $pct . '%</span>' .
                    '</div>',
                'action'         => '<div class="btn-action-group" style="white-space:nowrap;">' .
                    '<a href="' . route('budget-utilization.send-reminder', $b->id) . '" class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;" title="Send Reminder to LPI" data-bs-toggle="tooltip">' .
                    '<i class="fas fa-envelope" style="font-size:11px;"></i> Email' .
                    '</a></div>',
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
        ]);
    }

    /**
     * Sync budget data from external QU API.
     */
    public function sync()
    {
        $synced = 0;
        $errors = 0;

        try {
            // Step 1: Fetch project list from QU API
            $client = new \GuzzleHttp\Client(['verify' => false, 'timeout' => 30]);
            $listResponse = $client->get('https://residence.qu.edu.qa/ords/qucust/quapi/getProjects/123$$321');
            $listBody = json_decode($listResponse->getBody()->getContents(), true);
            $projects = $listBody['items'] ?? [];

            foreach ($projects as $item) {
                $projectNum = $item['project_num'] ?? null;
                if (!$projectNum) {
                    continue;
                }

                // Step 2: Fetch budget for each project
                try {
                    $budgetResponse = $client->get(
                        'https://residence.qu.edu.qa/ords/qucust/quapi/getProjectBudget/' . $projectNum . '/123$$321'
                    );
                    $budgetBody = json_decode($budgetResponse->getBody()->getContents(), true);
                    $budgetItems = $budgetBody['items'] ?? [];
                    $budgetData = $budgetItems[0] ?? null;

                    if ($budgetData) {
                        // Find matching project in our system by old_project_id
                        $project = Project::where('old_project_id', $projectNum)->first();

                        ProjectBudget::updateOrCreate(
                            ['project_num' => $projectNum],
                            [
                                'project_id'        => $project ? $project->id : null,
                                'project_name'      => $budgetData['project_name'] ?? $item['project_name'] ?? null,
                                'budget_amount'     => $budgetData['budget_amount'] ?? 0,
                                'actual_exp_amount' => $budgetData['actual_exp_amount'] ?? 0,
                                'commitment_amount' => $budgetData['committment_amount'] ?? 0,
                                'available_balance' => $budgetData['available_balance'] ?? 0,
                                'last_synced_at'    => now(),
                            ]
                        );
                        $synced++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                }
            }
        } catch (\Exception $e) {
            return redirect()->route('budget-utilization.index')
                ->with('error', 'API sync failed: ' . $e->getMessage());
        }

        return redirect()->route('budget-utilization.index')
            ->with('success', "Sync complete. {$synced} project(s) updated. {$errors} error(s).");
    }

    /**
     * Send budget utilization reminder email to the project's LPI.
     */
    public function sendReminder(ProjectBudget $budgetUtilization)
    {
        $project = Project::with('lpi', 'program.grant', 'program.cycle')->find($budgetUtilization->project_id);

        if (!$project || !$project->lpi) {
            return redirect()->route('budget-utilization.index')
                ->with('error', 'No LPI found for this project.');
        }

        $lpi = $project->lpi;
        $pct = $budgetUtilization->utilizationPercent();

        $subject = "Budget Utilization Reminder — Project " . ($project->old_project_id ?? $project->id);

        $body = "Dear {$lpi->name},\n\n";
        $body .= "This is a reminder regarding the budget utilization for your project:\n\n";
        $body .= "Project ID: " . ($project->old_project_id ?? $project->id) . "\n";
        $body .= "Project Title: {$project->title}\n";
        $body .= "Total Budget: QAR " . number_format($budgetUtilization->budget_amount, 2) . "\n";
        $body .= "Actual Expenditure: QAR " . number_format($budgetUtilization->actual_exp_amount, 2) . "\n";
        $body .= "Available Balance: QAR " . number_format($budgetUtilization->available_balance, 2) . "\n";
        $body .= "Utilization: {$pct}%\n\n";

        if ($pct < 50) {
            $body .= "Your project's budget utilization is below 50%. Please review your spending plan and ensure funds are being utilized effectively.\n\n";
        } else {
            $body .= "Your project is progressing well with budget utilization. Please continue to monitor expenditures.\n\n";
        }

        $body .= "Best regards,\nRTS Admin Team";

        $sender = auth()->user();

        try {
            // Log the email
            $logEntry = EmailSendLog::create([
                'sent_by'                => $sender->id,
                'recipient_email'        => $lpi->email,
                'recipient_name'         => $lpi->name,
                'subject'                => $subject,
                'body'                   => $body,
                'status'                 => 'queued',
            ]);

            // Queue the email
            Mail::to($lpi->email)
                ->queue(new GenericEmailMail(
                    $subject,
                    $body,
                    $sender->name,
                    $lpi->name
                ));

            $logEntry->update(['sent_at' => now()]);

            return redirect()->route('budget-utilization.index')
                ->with('success', "Budget reminder email sent to {$lpi->name}.");
        } catch (\Exception $e) {
            return redirect()->route('budget-utilization.index')
                ->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
