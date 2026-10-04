<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403);
            }
            return $next($request);
        })->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $announcements = Announcement::with('createdBy')->orderBy('created_at', 'desc')->get();
        } else {
            $role = $user->activeRole();
            $announcements = Announcement::with('createdBy')
                ->where(function ($q) use ($role) {
                    $q->whereNull('audience')
                      ->orWhere('audience', '')
                      ->orWhereRaw('LOWER(audience) IN (?, ?)', ['all', strtolower($role)]);
                })
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string|max:50',
            'audience' => 'nullable|in:All,LPI,Reviewer',
            'expires_at' => 'nullable|date',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active');

        $announcement = Announcement::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Announcement created successfully!', 'announcement' => $announcement]);
        }

        return redirect()->route('announcements.index')->with('success', 'Announcement created!');
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string|max:50',
            'audience' => 'nullable|in:All,LPI,Reviewer',
            'expires_at' => 'nullable|date',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $announcement->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Announcement updated successfully!', 'announcement' => $announcement]);
        }

        return redirect()->route('announcements.index')->with('success', 'Announcement updated!');
    }

    public function destroy($id)
    {
        Announcement::findOrFail($id)->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['message' => 'Announcement deleted successfully!']);
        }

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted!');
    }
}
