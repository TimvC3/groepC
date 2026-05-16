<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ZoningDesignation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use illuminate\Support\Str;


class ZoningDesignationController extends Controller
{
    public function index(): View
    {
        $functions = ZoningDesignation::orderBy('category')
            ->orderBy('name')
            ->get();

        return view('admin.functions.index', compact('functions'));
    }

    public function create()
    {
        return view('admin.functions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:security,recreation,environmental_quality,infrastructure,mobility'],
            'icon' => ['required', 'string', 'max:20'],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;

        while (ZoningDesignation::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $validated['slug'] = $slug;

        ZoningDesignation::create($validated);

        return redirect()
            ->route('admin.functions.index')
            ->with('success', 'Function created successfully.');
    }

    public function edit(ZoningDesignation $zoningDesignation): View
    {
        return view('admin.functions.edit', [
            'function' => $zoningDesignation,
        ]);
    }

    public function update(Request $request, ZoningDesignation $zoningDesignation): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:10'],
        ]);

        $zoningDesignation->update($validated);

        return redirect()
            ->route('admin.functions.index')
            ->with('success', 'Function updated successfully.');
    }
}