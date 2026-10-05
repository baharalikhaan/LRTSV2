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

        try {
            Mail::to($recipient->email)->queue(new GenericEmailMail(
                $subject,
                $body,
                self::SENDER_NAME,
                $recipient->name ?? ''
            ));

            EmailSendLog::create([
                'sent_by'         => ($actor ?? $recipient)->id,
                'recipient_email' => $recipient->email,
                'recipient_name'  => $recipient->name,
                'subject'         => $subject,
                'body'            => $body,
                'status'          => 'queued',
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("EventMailService: failed to send '{$eventKey}' to {$recipient->email}: " . $e->getMessage());
            return false;
        }
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
            '*cycle*'          => $cycleYear,
            '*deadline*'       => $deadline,
            '*grant_title*'    => $grantTitle,
            '*link*'           => route('projects.show', ['id' => $project->id]),
        ];
    }
}
