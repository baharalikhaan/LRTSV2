<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
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
     * List all email templates.
     */
    public function index()
    {
        $templates   = EmailTemplate::orderByDesc('updated_at')->get();
        $placeholders = EmailTemplate::availablePlaceholders();

        return view('admin.email-templates.index', compact('templates', 'placeholders'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.email-templates.create', [
            'categories'    => EmailTemplate::categories(),
            'placeholders'  => EmailTemplate::availablePlaceholders(),
        ]);
    }

    /**
     * Store a new template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'subject'   => 'required|string|max:255',
            'body'      => 'required|string|max:10000',
            'signature' => 'nullable|string|max:2000',
            'category'  => 'required|string|max:50',
        ]);

        EmailTemplate::create($validated);

        return redirect()->route('email-templates.index')->with('success', 'Template created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.email-templates.edit', [
            'template'      => $emailTemplate,
            'categories'    => EmailTemplate::categories(),
            'placeholders'  => EmailTemplate::availablePlaceholders(),
        ]);
    }

    /**
     * Update an existing template.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'subject'   => 'required|string|max:255',
            'body'      => 'required|string|max:10000',
            'signature' => 'nullable|string|max:2000',
            'category'  => 'required|string|max:50',
        ]);

        $emailTemplate->update($validated);

        return redirect()->route('email-templates.index')->with('success', 'Template updated successfully.');
    }

    /**
     * Delete a template.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return redirect()->route('email-templates.index')->with('success', 'Template deleted.');
    }

    /**
     * Preview a template rendered with sample data (AJAX).
     */
    public function preview(EmailTemplate $emailTemplate)
    {
        $sampleData = [
            '*name*'          => 'Dr. Ahmed Ali',
            '*email*'         => 'ahmed@qu.edu.qa',
            '*old_project_id*' => 'QU-2026-0123',
            '*project_title*'  => 'Advanced Research in AI',
            '*cycle*'          => '2026',
            '*deadline*'       => now()->addDays(30)->format('d M Y'),
            '*grant_title*'    => 'Regular Grant',
            '*link*'           => url('/projects'),
        ];

        $subject = $emailTemplate->subject;
        $body    = $emailTemplate->render($sampleData);
        $sig     = $emailTemplate->signature;

        return response()->json([
            'subject' => $subject,
            'body'    => $body,
            'signature' => $sig,
        ]);
    }
}
