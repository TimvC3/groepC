<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Facility;
use Illuminate\Http\Request;
use App\Models\FacilityScore;
use Illuminate\Http\JsonResponse;

class FacilityController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get();
 
        $facilities = Facility::with([
            'category',
            'scores.category',
        ])
        ->orderBy('sort_order')
        ->get();
 
        return view('grid.facilities', compact('facilities', 'categories'));
    }

    public function update(Request $request, FacilityScore $facilityScore): JsonResponse
    {
        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:-5', 'max:5'],
        ]);
 
        $facilityScore->update($validated);
 
        return response()->json([
            'score' => $facilityScore->score,
        ]);
    }
}