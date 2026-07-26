<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::orderBy('tag', 'asc')->get();
        return view('tags.index', compact('tags'));
    }

    public function create()
    {
        return view('tags.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tag' => 'required|string|max:255|unique:tags,tag',
        ]);

        $tag = Tag::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Tag created successfully.', 'tag' => $tag]);
        }

        return redirect()->route('tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function edit(Tag $tag)
    {
        return view('tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'tag' => 'required|string|max:255|unique:tags,tag,' . $tag->id,
        ]);

        $tag->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Tag updated successfully.', 'tag' => $tag]);
        }

        return redirect()->route('tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}
