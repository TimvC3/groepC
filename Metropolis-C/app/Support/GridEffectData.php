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
                    'slug' => $category->slug,
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
            'facilities' => $facilities
                ->mapWithKeys(fn (Facility $facility) => [
                    $facility->id => [
                        'id' => $facility->id,
                        'name' => $facility->name,
                        'slug' => $facility->slug,
                        'categoryId' => $facility->category_id,
                        'categorySlug' => $facility->category->slug,
                        'scores' => $categories
                            ->mapWithKeys(fn (Category $category) => [
                                $category->id => (int) (
                                    $facility->scores->firstWhere('category_id', $category->id)?->score ?? 0
                                ),
                            ])
                            ->all(),
                    ],
                ]),
        ];
    }
}
