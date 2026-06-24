<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityCondition;
use Illuminate\Support\Collection;

class GridEffectData
{
    public static function from(
        Collection $categories,
        Collection $facilities,
        ?Collection $conditions = null
    ): array {
        $requiredConditions = ($conditions ?? collect())
            ->where('condition_type', FacilityCondition::REQUIRED_NEIGHBOUR)
            ->keyBy('facility_id');
        $neighbourRules = $facilities
            ->filter(fn (Facility $facility) => $requiredConditions->has($facility->id))
            ->mapWithKeys(function (Facility $facility) use ($requiredConditions, $facilities): array {
                $condition = $requiredConditions->get($facility->id);

                return [
                    $facility->id => [
                        'requiredNeighbourId' => (int) $condition->neighbour_facility_id,
                        'requiredNeighbourName' => $condition->neighbourFacility?->name
                            ?? $facilities->firstWhere('id', $condition->neighbour_facility_id)?->name
                            ?? 'Required neighbour',
                    ],
                ];
            })
            ->all();

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

            'neighbourRules' => $neighbourRules,
        ];
    }
}
