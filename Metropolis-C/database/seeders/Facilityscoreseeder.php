<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilityScoreSeeder extends Seeder
{
    /**
     * Score matrix: rows = facilities, columns = categories:
     * [Security, Recreation, Environmental Quality, Facilities, Mobility]
     *
     * Scores range from -5 (very negative impact) to 5 (very positive impact).
     */
    public function run(): void
    {
        $facilities = DB::table('facilities')->pluck('id', 'slug');
        $categories = DB::table('categories')->pluck('id', 'slug');

        // [facility_slug => [security, recreation, environmental-quality, facilities, mobility]]
        $matrix = [
            'police-station' => [5, 1, 0, 1, 2],
            'fire-station' => [4, 1, 2, 1, 2],
            'park' => [-2, 5, 4, 0, 0],
            'cinema' => [-1, 4, 0, 2, 0],
            'sports-park' => [0, 5, 2, 3, 0],
            'water-purification' => [0, 0, 5, 2, 0],
            'school' => [2, 2, 0, 5, -3],
            'store' => [0, 0, -2, 5, 0],
            'hospital' => [3, 0, 0, 5, 0],
            'train-station' => [-2, 2, 0, 4, 5],
            'road' => [-4, 2, -4, 3, 5],
            'cycling-path' => [0, 3, 3, 3, 4],
            'petrol-station' => [-2, 0, -4, 1, 4],
        ];

        $categoryOrder = [
            'security',
            'recreation',
            'environmental-quality',
            'facilities',
            'mobility',
        ];

        $rows = [];

        foreach ($matrix as $facilitySlug => $scores) {
            foreach ($categoryOrder as $index => $categorySlug) {
                $rows[] = [
                    'facility_id' => $facilities[$facilitySlug],
                    'category_id' => $categories[$categorySlug],
                    'score' => $scores[$index],
                ];
            }
        }

        DB::table('facility_scores')->insert($rows);
    }
}
