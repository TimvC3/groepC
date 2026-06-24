<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityCondition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FacilityConditionController extends Controller
{
    public function store(Request $request, ?Facility $facility = null): RedirectResponse
    {
        $attributes = $this->validatedAttributes($request, $facility);

        if ($this->conditionExists($attributes)) {
            return back()->withErrors([
                $this->neighbourField($request) => 'This condition already exists.',
            ]);
        }

        FacilityCondition::create($attributes);

        return back()->with('success', 'Condition created successfully.');
    }

    public function update(
        Request $request,
        ?Facility $facility = null,
        ?FacilityCondition $condition = null
    ): RedirectResponse {
        $condition ??= $request->route('condition');
        abort_unless($condition instanceof FacilityCondition, 404);

        if ($facility && $condition->facility_id !== $facility->id) {
            abort(404);
        }

        $attributes = $this->validatedAttributes($request, $facility);

        if ($this->conditionExists($attributes, $condition)) {
            return back()->withErrors([
                $this->neighbourField($request) => 'This condition already exists.',
            ]);
        }

        $condition->update($attributes);

        return back()->with('success', 'Condition updated successfully.');
    }

    public function destroy(
        Request $request,
        ?Facility $facility = null,
        ?FacilityCondition $condition = null
    ): RedirectResponse {
        $condition ??= $request->route('condition');
        abort_unless($condition instanceof FacilityCondition, 404);

        if ($facility && $condition->facility_id !== $facility->id) {
            abort(404);
        }

        $condition->delete();

        return back()->with('success', 'Condition deleted successfully.');
    }

    private function validatedAttributes(Request $request, ?Facility $facility): array
    {
        $facilityId = $facility?->id ?? $request->input('facility_id');
        $typeField = $request->has('condition_type') ? 'condition_type' : 'type';
        $neighbourField = $this->neighbourField($request);

        $validated = $request->validate([
            $typeField => [
                'required',
                Rule::in([
                    FacilityCondition::REQUIRED_NEIGHBOUR,
                    FacilityCondition::FORBIDDEN_NEIGHBOUR,
                ]),
            ],
            $neighbourField => [
                'required',
                'integer',
                'exists:facilities,id',
                Rule::notIn([(int) $facilityId]),
            ],
            ...($facility ? [] : [
                'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            ]),
        ]);

        $type = $validated[$typeField];
        $neighbourId = (int) $validated[$neighbourField];
        $facilityId = (int) $facilityId;

        if (! $facility && $type === FacilityCondition::FORBIDDEN_NEIGHBOUR) {
            return [
                'facility_id' => min($facilityId, $neighbourId),
                'condition_type' => $type,
                'neighbour_facility_id' => max($facilityId, $neighbourId),
            ];
        }

        return [
            'facility_id' => $facilityId,
            'condition_type' => $type,
            'neighbour_facility_id' => $neighbourId,
        ];
    }

    private function neighbourField(Request $request): string
    {
        return $request->has('neighbour_facility_id')
            ? 'neighbour_facility_id'
            : 'related_facility_id';
    }

    private function conditionExists(
        array $attributes,
        ?FacilityCondition $ignoredCondition = null
    ): bool {
        return FacilityCondition::query()
            ->where('facility_id', $attributes['facility_id'])
            ->where('condition_type', $attributes['condition_type'])
            ->where('neighbour_facility_id', $attributes['neighbour_facility_id'])
            ->when(
                $ignoredCondition,
                fn ($query) => $query->whereKeyNot($ignoredCondition->id)
            )
            ->exists();
    }
}
