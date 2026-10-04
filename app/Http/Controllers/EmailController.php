<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailSendLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\GenericEmailMail;

class EmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || !$user->isAdmin()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Unauthorized.'], 403);
                }
                return redirect()->route('home')->with('error', 'Unauthorized. Only admins can access this page.');
            }
            return $next($request);
        });
    }

    /**
     * Show the email compose form with recent send log.
     */
    public function compose()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'type']);
        $emailLogs = EmailSendLog::with('sender')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('admin.send-email', compact('users', 'emailLogs'));
    }

    /**
     * Send the email to selected recipients with optional attachment.
     */
    public function send(Request $request)
    {
        // Debugging aid (safe to keep): records every request that reaches the
        // controller — if nothing appears in storage/logs/laravel.log after a
        // click, the request died in the browser/webserver layer (CSRF, route,
        // or web server), not in this code.
        \Log::info('Send Email: request received', [
            'by_user'        => auth()->id(),
            'recipient_ids'  => $request->input('recipients', []),
            'cc'             => $request->input('cc'),
            'subject'        => $request->input('subject'),
            'body_length'    => strlen((string) $request->input('body')),
            'has_attachment' => $request->hasFile('attachment'),
        ]);

        // Manual admin emails are NOT governed by the global MAIL_ENABLED
        // switch — that switch only silences automatic/system notifications
        // (reminders, rejection alerts, etc.). Manual sends always attempt a
        // real SMTP delivery; failures are recorded in the send log.

        $request->validate([
            'recipients'   => 'required|array|min:1',
            'recipients.*' => 'required|exists:users,id',
            'cc'           => 'nullable|string|max:500',
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string|max:10000',
            'attachment'   => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip',
        ]);

        $recipients = User::whereIn('id', $request->recipients)->get();
        $cc = $request->input('cc') ? trim($request->input('cc')) : null;
        $subject = $request->input('subject');
        $body = $request->input('body');
        $sender = auth()->user();
        $senderName = $sender->name;

        // Handle attachment
        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $storedName = uniqid() . '_' . $attachmentName;
            // Store under storage/app/email-attachments/ (matches GenericEmailMail lookup)
            $file->storeAs('email-attachments', $storedName);
            $attachmentPath = 'email-attachments/' . $storedName;
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($recipients as $recipient) {
            $logEntry = EmailSendLog::create([
                'sent_by'                => $sender->id,
                'recipient_email'        => $recipient->email,
                'recipient_name'         => $recipient->name,
                'cc'                     => $cc,
                'subject'                => $subject,
                'body'                   => $body,
                'attachment_path'        => $attachmentPath,
                'attachment_original_name' => $attachmentName,
                'status'                 => 'queued',
            ]);

            try {
                // Send synchronously — shared hosting has no queue worker, so
                // queued mails would sit in the jobs table forever (and the
                // jobs table doesn't even exist). Sync also surfaces real
                // SMTP errors immediately in the send log below.
                // Mail::mailer('smtp') bypasses the global array-transport
                // override (AppServiceProvider) so manual sends go out even
                // while automatic emails are switched off.
                Mail::mailer('smtp')
                    ->to($recipient->email)
                    ->cc($cc ? array_map('trim', explode(',', $cc)) : [])
                    ->send(new GenericEmailMail($subject, $body, $senderName, $recipient->name, $attachmentPath, $attachmentName));

                $logEntry->update(['status' => 'sent', 'sent_at' => now()]);
                $sentCount++;
            } catch (\Exception $e) {
                $logEntry->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $sentCount,
                'failed'  => $failedCount,
                'message' => "{$sentCount} email(s) sent. {$failedCount} failed.",
            ]);
        }

        $message = "{$sentCount} email(s) sent successfully.";
        if ($failedCount > 0) {
            $message .= " {$failedCount} failed.";
        }

        return redirect()->route('admin.send-email')->with('success', $message);
    }

    /**
     * Retry a failed email send.
     */
    public function retry(EmailSendLog $log)
    {
        if ($log->status !== 'failed') {
            return response()->json(['success' => false, 'message' => 'Only failed emails can be retried.'], 422);
        }

        try {
            $cc = $log->cc ? array_map('trim', explode(',', $log->cc)) : [];

            Mail::mailer('smtp')
                ->to($log->recipient_email)
                ->cc($cc)
                ->send(new GenericEmailMail(
                    $log->subject,
                    $log->body,
                    $log->sender ? $log->sender->name : 'RTS Admin',
                    $log->recipient_name ?: $log->recipient_email,
                    $log->attachment_path,
                    $log->attachment_original_name
                ));

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);

            return response()->json(['success' => true, 'message' => 'Email sent successfully.']);
        } catch (\Exception $e) {
            $log->update(['error_message' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Retry failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Get users filtered by role (AJAX endpoint).
     */
    public function getUsers(Request $request)
    {
        $role = $request->input('role');

        $query = User::orderBy('name');

        if ($role === 'lpi') {
            $query->where(function ($q) {
                $q->where('type', 'LPI')
                  ->orWhere('type', 'LPI+Reviewer')
                  ->orWhere('type', 'Admin+LPI+Reviewer');
            });
        } elseif ($role === 'reviewer') {
            $query->where(function ($q) {
                $q->where('type', 'Reviewer')
                  ->orWhere('type', 'LPI+Reviewer')
                  ->orWhere('type', 'Admin+LPI+Reviewer');
            });
        } elseif ($role === 'admin') {
            $query->where(function ($q) {
                $q->where('type', 'Admin')
                  ->orWhere('type', 'Admin+LPI+Reviewer');
            });
        }

        $users = $query->get(['id', 'name', 'email', 'type']);

        return response()->json($users);
    }
}
