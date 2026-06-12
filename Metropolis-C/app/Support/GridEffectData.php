<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Facility;
use Illuminate\Support\Collection;

class GridEffectData
{
    public static function from(Collection $categories, Collection $facilities): array
    {
        return [
            'categories' => $categories
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])
                ->values(),
            'scoreMatrix' => $facilities
                ->mapWithKeys(fn (Facility $facility) => [
                    $facility->id => $categories
                        ->mapWithKeys(fn (Category $category) => [
                            $category->id => (int) (
                                $facility->scores->firstWhere('category_id', $category->id)?->score ?? 0
                            ),
                        ])
                        ->all(),
                ]),
        ];
    }
}
