<?php

namespace App\Http\Controllers;

use App\Mail\NewFacilityCreated;
use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FacilityController extends Controller
{
    public function index(): View
    {
        return $this->facilitiesView();
    }

    public function edit(Facility $facility): View
    {
        $facility->load(['category', 'scores.category', 'conditions.neighbourFacility']);

        return $this->facilitiesView($facility);
    }

    private function facilitiesView(?Facility $editingFacility = null): View
    {
        $categories = Category::orderBy('sort_order')->get();

        $facilities = Facility::with([
            'category',
            'scores.category',
            'conditions.neighbourFacility',
        ])
            ->orderBy('sort_order')
            ->get();

        return view('grid.facilities', compact('facilities', 'categories', 'editingFacility'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'icon' => ['nullable', 'string', 'max:20'],
            'scores' => ['array'],
            'scores.*' => ['required', 'integer', 'min:-5', 'max:5'],
        ]);

        $facility = DB::transaction(function () use ($validated) {
            $facility = Facility::create([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug($validated['name']),
                'icon' => $validated['icon'] ?? null,
                'sort_order' => (Facility::max('sort_order') ?? 0) + 1,
            ]);

            Category::orderBy('sort_order')->each(function (Category $category) use ($facility, $validated) {
                FacilityScore::create([
                    'facility_id' => $facility->id,
                    'category_id' => $category->id,
                    'score' => (int) ($validated['scores'][$category->id] ?? 0),
                ]);
            });

            return $facility;
        });

        Mail::to(env('EXPERT_EMAIL'))->send(new NewFacilityCreated($facility->load(['category', 'scores.category'])));

        return redirect()
            ->route('facilities')
            ->with('success', "{$facility->name} was created successfully.");
    }

    public function updateFacility(Request $request, Facility $facility): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'icon' => ['nullable', 'string', 'max:20'],
            'scores' => ['array'],
            'scores.*' => ['required', 'integer', 'min:-5', 'max:5'],
        ]);

        DB::transaction(function () use ($facility, $validated) {
            $facility->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug($validated['name'], $facility),
                'icon' => $validated['icon'] ?? null,
            ]);

            Category::orderBy('sort_order')->each(function (Category $category) use ($facility, $validated) {
                FacilityScore::updateOrCreate(
                    [
                        'facility_id' => $facility->id,
                        'category_id' => $category->id,
                    ],
                    [
                        'score' => (int) ($validated['scores'][$category->id] ?? 0),
                    ],
                );
            });
        });

        return redirect()
            ->route('facilities')
            ->with('success', "{$facility->name} was updated successfully.");
    }

    public function update(Request $request, FacilityScore $facilityScore): JsonResponse
    {
        abort_if($request->user()?->role === 'policy_maker', 403);

        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:-5', 'max:5'],
        ]);

        $facilityScore->update($validated);

        return response()->json([
            'score' => $facilityScore->score,
        ]);
    }

    private function uniqueSlug(string $name, ?Facility $ignoreFacility = null): string
    {
        $baseSlug = Str::slug($name) ?: 'facility';
        $slug = $baseSlug;
        $counter = 1;

        while (Facility::where('slug', $slug)
            ->when($ignoreFacility, fn ($query) => $query->whereKeyNot($ignoreFacility->id))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
