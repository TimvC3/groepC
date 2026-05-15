<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Facility;
use App\Support\GridEffectData;

class GridController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->get();

        $facilities = Facility::with(['category', 'scores.category'])
            ->orderBy('sort_order')
            ->get();

        $groupedFacilities = $facilities->groupBy('category.name');
        $effectData = GridEffectData::from($categories, $facilities);

        return view('grid.grid', compact(
            'categories',
            'facilities',
            'groupedFacilities',
            'effectData',
        ));
    }
}
