<?php

namespace App\Http\Controllers;

use App\Mail\NewFacilityCreated;
use App\Models\ApprovedGridCell;
use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityCondition;
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
        return $this->functionsView();
    }

    public function edit(Facility $facility): View
    {
        $facility->load([
            'category',
            'scores.category',
            'conditions.neighbourFacility',
        ]);

        return $this->functionsView($facility);
    }

    private function functionsView(?Facility $editingFacility = null): View
    {
        $categories = Category::orderBy('sort_order')->get();
        $approvedFacilityIds = ApprovedGridCell::query()
            ->where('item_type', 'facility')
            ->pluck('item_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $functions = Facility::with([
            'category',
            'scores.category',
            'conditions.neighbourFacility',
        ])
            ->orderBy('sort_order')
            ->get();

        $conditions = FacilityCondition::with(['facility', 'neighbourFacility'])
            ->orderBy('facility_id')
            ->orderBy('condition_type')
            ->get();

        return view('grid.facilities', compact(
            'functions',
            'categories',
            'editingFacility',
            'conditions',
            'approvedFacilityIds'
        ));
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

        $expertEmail = env('EXPERT_EMAIL');

        if ($expertEmail) {
            Mail::to($expertEmail)->send(new NewFacilityCreated($facility->load([
                'category',
                'scores.category',
            ])));
        }

        return redirect()
            ->route('functions.index')
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
            ->route('functions.index')
            ->with('success', "{$facility->name} was updated successfully.");
    }

    public function update(Request $request, FacilityScore $facilityScore): JsonResponse
    {
        if ($this->scoreBelongsToApprovedFacility($facilityScore)) {
            return response()->json([
                'message' => 'This destination has already been approved and its effects can no longer be changed.',
            ], 403);
        }

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

    private function scoreBelongsToApprovedFacility(FacilityScore $facilityScore): bool
    {
        return ApprovedGridCell::query()
            ->where('item_type', 'facility')
            ->where('item_id', $facilityScore->facility_id)
            ->exists();
    }
}
