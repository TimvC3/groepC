<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Facility;

class GridController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get();

        $facilities = Facility::with(['category', 'scores.category'])
            ->orderBy('sort_order')
            ->get();

        $groupedFacilities = $facilities->groupBy('category.name');

        $effectCategories = $categories
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ]);

        $facilityScoreMatrix = $facilities
            ->mapWithKeys(fn (Facility $facility) => [
                $facility->id => $categories
                    ->mapWithKeys(fn (Category $category) => [
                        $category->id => (int) ($facility->scores->firstWhere('category_id', $category->id)?->score ?? 0),
                    ])
                    ->all(),
            ]);

        return view('grid.grid', compact(
            'categories',
            'facilities',
            'groupedFacilities',
            'effectCategories',
            'facilityScoreMatrix',
        ));
    }
}
