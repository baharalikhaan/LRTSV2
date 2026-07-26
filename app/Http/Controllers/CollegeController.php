<?php

namespace App\Http\Controllers;

use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollegeController extends Controller
{
    public function index()
    {
        $colleges = College::orderBy('name', 'asc')->get();
        return view('colleges.index', compact('colleges'));
    }

    public function create()
    {
        return view('colleges.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:colleges,code',
            'name' => 'required|string|max:255|unique:colleges,name',
        ]);

        $college = College::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'College created successfully.', 'college' => $college]);
        }

        return redirect()->route('colleges.index')
            ->with('success', 'College created successfully.');
    }

    public function edit(College $college)
    {
        return view('colleges.edit', compact('college'));
    }

    public function update(Request $request, College $college)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('colleges', 'code')->ignore($college->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('colleges', 'name')->ignore($college->id)],
        ]);

        $college->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'College updated successfully.', 'college' => $college]);
        }

        return redirect()->route('colleges.index')
            ->with('success', 'College updated successfully.');
    }

    public function destroy(College $college)
    {
        $college->delete();

        return redirect()->route('colleges.index')
            ->with('success', 'College deleted successfully.');
    }
}
