<?php

namespace App\Http\Controllers;

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