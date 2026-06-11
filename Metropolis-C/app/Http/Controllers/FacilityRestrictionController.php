<?php

namespace App\Http\Controllers;

use App\Models\FacilityRestriction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FacilityRestrictionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'facility_id_1' => ['required', 'integer', 'exists:facilities,id'],
            'facility_id_2' => ['required', 'integer', 'exists:facilities,id', 'different:facility_id_1'],
        ]);

        $id1 = min($validated['facility_id_1'], $validated['facility_id_2']);
        $id2 = max($validated['facility_id_1'], $validated['facility_id_2']);

        if (FacilityRestriction::where('facility_id_1', $id1)->where('facility_id_2', $id2)->exists()) {
            return redirect()->route('facilities')->with('error', 'This restriction already exists.');
        }

        FacilityRestriction::create(['facility_id_1' => $id1, 'facility_id_2' => $id2]);

        return redirect()->route('facilities')->with('success', 'Restriction added successfully.');
    }

    public function destroy(FacilityRestriction $restriction): RedirectResponse
    {
        $restriction->delete();

        return redirect()->route('facilities')->with('success', 'Restriction removed successfully.');
    }
}
