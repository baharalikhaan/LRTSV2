<?php

namespace App\Services;

use App\Mail\GenericEmailMail;
use App\Models\EmailSendLog;
use App\Models\EmailTemplate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends event-based (automatic) notifications to LPIs and reviewers.
 *
 * Each notification is driven by an editable EmailTemplate addressed by its
 * machine key (see EmailTemplate::EVENTS). When MAIL_ENABLED is off, nothing
 * is sent and no send-log rows are written. The mail is delivered from the
 * system identity but, because email_send_log.sent_by is a required FK, the
 * acting user (admin/LPI who triggered the event) is recorded as the sender.
 */
class EventMailService
{
    public const SENDER_NAME = 'Research Tracking System';

    /**
     * "Action required" steps rendered after the project details, per event.
     * Only the events that ask the recipient to do something in RTS carry
     * steps; the others rely on their template body alone.
     */
    public const ACTION_POINTS = [
        'reviewer_assigned' => [
            'Evaluate the Progress Report against the approved proposal using the provided rubric.',
            'Provide scores and detailed feedback justifying your assessment.',
            'Recommend whether the project should Continue or Be Discontinued [with justification].',
        ],
    ];

    /**
     * Events whose email should carry the off-campus access disclaimer.
     */
    public const DISCLAIMER_EVENTS = ['reviewer_assigned'];

    /**
     * Events whose email should point the recipient at the support mailbox.
     */
    public const SUPPORT_EVENTS = ['reviewer_assigned'];

    /**
     * Project detail rows to leave out of an event's email. The reviewer
     * assignment email only identifies the project (ID, title, research call);
     * the grant, the cycle year and the report deadlines belong to the LPI.
     */
    public const HIDDEN_DETAILS = [
        'reviewer_assigned' => GenericEmailMail::REVIEWER_HIDDEN_ROWS,
    ];

    /**
     * Send the given event template to a recipient in the context of a project.
     *
     * @param  string  $eventKey  One of EmailTemplate::eventKeys()
     * @param  User    $recipient
     * @param  Project $project
     * @param  User|null $actor   User recorded as the log sender (defaults to recipient)
     * @param  array   $extra     Extra placeholder data (e.g. a report label)
     * @param  bool    $force     Send even when MAIL_ENABLED is off (manual flows)
     */
    public function send(string $eventKey, User $recipient, Project $project, ?User $actor = null, array $extra = [], bool $force = false): bool
    {
        if (!$recipient || !$recipient->email) {
            return false;
        }

        // Global kill switch: automatic event mail is suppressed (and not
        // logged) when MAIL_ENABLED is off.
        if (!$force && !config('mail.enabled')) {
            return false;
        }

        $template = EmailTemplate::findByKey($eventKey);
        if (!$template) {
            Log::warning("EventMailService: no template found for key '{$eventKey}'");
            return false;
        }

        $data = array_merge($this->placeholderData($project, $recipient), $extra);

        $subject = $template->renderSubject($data);
        $body    = $template->renderBody($data);

        $signature = trim($template->renderSignature($data));
        if ($signature !== '') {
            $body .= "\n\n" . $signature;
        }

        // Log first (fast), then defer the actual SMTP delivery until after the
        // HTTP response has been sent. With QUEUE_CONNECTION=sync a plain
        // Mail::queue() would block the request on the SMTP handshake — a slow
        // or unreachable mail host made actions like "register project" hang
        // for ~20s. Deferring keeps the UI instant regardless of mail health.
        $log = EmailSendLog::create([
            'sent_by'         => ($actor ?? $recipient)->id,
            'recipient_email' => $recipient->email,
            'recipient_name'  => $recipient->name,
            'subject'         => $subject,
            'body'            => $body,
            'status'          => 'queued',
        ]);

        $mailable = new GenericEmailMail(
            $subject,
            $body,
            self::SENDER_NAME,
            $recipient->name ?? '',
            null,
            null,
            [
                'heading'       => EmailTemplate::EVENTS[$eventKey]['name'] ?? 'Notification',
                'project'       => self::projectDetailsFor($eventKey, $project),
                'action_label'  => 'Open the project',
                'action_points' => self::ACTION_POINTS[$eventKey] ?? [],
                'disclaimer'    => in_array($eventKey, self::DISCLAIMER_EVENTS, true)
                    ? GenericEmailMail::DISCLAIMER
                    : null,
                'support_email' => in_array($eventKey, self::SUPPORT_EVENTS, true)
                    ? GenericEmailMail::SUPPORT_EMAIL
                    : null,
            ]
        );

        $deliver = function () use ($recipient, $mailable, $log) {
            try {
                Mail::to($recipient->email)->send($mailable);
                $log->update(['status' => 'sent', 'sent_at' => now()]);
            } catch (\Throwable $e) {
                $log->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                Log::error("EventMailService: failed to send to {$recipient->email}: " . $e->getMessage());
            }
        };

        // Run after the response is flushed when possible; fall back to an
        // immediate send in CLI contexts (no HTTP response lifecycle).
        if (app()->runningInConsole()) {
            $deliver();
        } else {
            app()->terminating($deliver);
        }

        return true;
    }

    /**
     * Project details for an event's email: the shared context minus the rows
     * that event does not need (see HIDDEN_DETAILS).
     */
    public static function projectDetailsFor(string $eventKey, Project $project): array
    {
        return GenericEmailMail::projectContext($project, self::HIDDEN_DETAILS[$eventKey] ?? []);
    }

    /**
     * Build the placeholder replacement data for a project/recipient.
     */
    protected function placeholderData(Project $project, User $recipient): array
    {
        $program = $project->program;

        $grantTitle = '';
        if ($program) {
            if ($program->grant && $program->grant->grant_name) {
                $grantTitle = $program->grant->grant_name;
            } elseif ($program->program_title) {
                $grantTitle = $program->program_title;
            }
        }

        $cycleYear = $program && $program->cycle ? ($program->cycle->year ?? '') : '';
        $deadline  = $program && $program->final_rpt_deadline
            ? $program->final_rpt_deadline->format('d M Y')
            : '';

        return [
            '*name*'           => $recipient->name ?? '',
            '*email*'          => $recipient->email ?? '',
            '*old_project_id*' => $project->old_project_id ?: $project->id,
            '*project_title*'  => $project->title ?? '',
            '*program_title*'  => $program->program_title ?? '',
            '*cycle*'          => $cycleYear,
            '*deadline*'       => $deadline,
            '*grant_title*'    => $grantTitle,
            '*link*'           => route('projects.show', ['id' => $project->id]),
        ];
    }
}
