<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\FacilityCondition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacilityConditionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCondition($request);
        $attributes = $this->normalizedAttributes($validated);

        if ($this->conditionExists($attributes)) {
            return back()->with('error', 'This condition already exists.');
        }

        FacilityCondition::create($attributes);

        return back()->with('success', 'Condition created successfully.');
    }

    public function update(Request $request, FacilityCondition $condition): RedirectResponse
    {
        $validated = $this->validateCondition($request);
        $attributes = $this->normalizedAttributes($validated);

        if ($this->conditionExists($attributes, $condition)) {
            return back()->with('error', 'This condition already exists.');
        }

        $condition->update($attributes);

        return redirect()->route('facilities')->with('success', 'Condition updated successfully.');
    }

    public function destroy(FacilityCondition $condition): RedirectResponse
    {
        $condition->delete();

        return back()->with('success', 'Condition deleted successfully.');
    }

    private function validateCondition(Request $request): array
    {
        return $request->validate([
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'type' => [
                'required',
                Rule::in([
                    FacilityCondition::REQUIRED_NEIGHBOUR,
                    FacilityCondition::FORBIDDEN_NEIGHBOUR,
                ]),
            ],
            'related_facility_id' => [
                'required',
                'integer',
                'exists:facilities,id',
                'different:facility_id',
            ],
        ]);
    }

    private function normalizedAttributes(array $validated): array
    {
        if ($validated['type'] === FacilityCondition::FORBIDDEN_NEIGHBOUR) {
            return [
                'facility_id' => min($validated['facility_id'], $validated['related_facility_id']),
                'condition_type' => $validated['type'],
                'neighbour_facility_id' => max($validated['facility_id'], $validated['related_facility_id']),
            ];
        }

        return [
            'facility_id' => $validated['facility_id'],
            'condition_type' => $validated['type'],
            'neighbour_facility_id' => $validated['related_facility_id'],
        ];
    }

    private function conditionExists(
        array $attributes,
        ?FacilityCondition $ignoredCondition = null
    ): bool {
        return FacilityCondition::query()
            ->where('facility_id', $attributes['facility_id'])
            ->where('condition_type', $attributes['condition_type'])
            ->when(
                $attributes['condition_type'] === FacilityCondition::FORBIDDEN_NEIGHBOUR,
                fn ($query) => $query->where(
                    'neighbour_facility_id',
                    $attributes['neighbour_facility_id']
                )
            )
            ->when(
                $ignoredCondition,
                fn ($query) => $query->whereKeyNot($ignoredCondition->id)
            )
            ->exists();
    }
}
=======
use App\Http\Requests\StoreFacilityConditionRequest;
use App\Http\Requests\UpdateFacilityConditionRequest;
use App\Models\Facility;
use App\Models\FacilityCondition;
use Illuminate\Http\RedirectResponse;

class FacilityConditionController extends Controller
{
    public function store(
        StoreFacilityConditionRequest $request,
        Facility $facility
    ): RedirectResponse {
        $facility->conditions()->create($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Condition was created successfully.');
    }

    public function update(
        UpdateFacilityConditionRequest $request,
        Facility $facility,
        FacilityCondition $condition
    ): RedirectResponse {
        abort_unless($condition->facility_id === $facility->id, 404);

        $condition->update($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Condition was updated successfully.');
    }

    public function destroy(
        Facility $facility,
        FacilityCondition $condition
    ): RedirectResponse {
        abort_unless($condition->facility_id === $facility->id, 404);

        $condition->delete();

        return redirect()
            ->back()
            ->with('success', 'Condition was deleted successfully.');
    }
}
>>>>>>> 9b1fb50cbd837e928587d63b45472150bab40073
