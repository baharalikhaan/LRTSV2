<?php

namespace App\Http\Controllers;

use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollegeController extends Controller
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
        $colleges = College::with('departments')->orderBy('name', 'asc')->get();
        return view('colleges.index', compact('colleges'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:colleges,code',
            'name' => 'required|string|max:255|unique:colleges,name',
            'departments' => 'nullable|array',
            'departments.*' => 'nullable|string|max:255',
        ]);

        $college = College::create($validated);
        $this->syncDepartments($college, $request->input('departments', []));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'College created successfully.', 'college' => $college->load('departments')]);
        }

        return redirect()->route('colleges.index')
            ->with('success', 'College created successfully.');
    }

    public function update(Request $request, College $college)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('colleges', 'code')->ignore($college->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('colleges', 'name')->ignore($college->id)],
            'departments' => 'nullable|array',
            'departments.*' => 'nullable|string|max:255',
        ]);

        $college->update($validated);
        $this->syncDepartments($college, $request->input('departments', []));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'College updated successfully.', 'college' => $college->load('departments')]);
        }

        return redirect()->route('colleges.index')
            ->with('success', 'College updated successfully.');
    }

    /**
     * Sync the college's departments against the submitted list:
     * keeps existing entries that are still present, creates new ones,
     * and deletes the ones the user removed.
     */
    private function syncDepartments(College $college, array $submitted): void
    {
        $names = collect($submitted)
            ->map(fn ($d) => trim((string) $d))
            ->filter()
            ->unique(fn ($d) => mb_strtolower($d))
            ->values();

        $existing = $college->departments()->get()->keyBy(fn ($d) => mb_strtolower($d->name));

        foreach ($names as $name) {
            $key = mb_strtolower($name);
            if ($existing->has($key)) {
                $existing->forget($key); // unchanged — keep
            } else {
                $college->departments()->create(['name' => $name]);
            }
        }

        $existing->each->delete(); // whatever remains was removed by the user
    }

    public function destroy(College $college)
    {
        $college->delete();

        return redirect()->route('colleges.index')
            ->with('success', 'College deleted successfully.');
    }
}
