<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilityScoreSeeder extends Seeder
{
    /**
     * Score matrix — rows = facilities, columns = categories:
     * [Veiligheid, Recreatie, Milieukwaliteit, Voorzieningen, Mobiliteit]
     *
     * Scores range from -5 (very negative impact) to 5 (very positive impact).
     */
    public function run(): void
    {
        $facilities  = DB::table('facilities')->pluck('id', 'slug');
        $categories  = DB::table('categories')->pluck('id', 'slug');

        // [facility_slug => [veiligheid, recreatie, milieukwaliteit, voorzieningen, mobiliteit]]
        $matrix = [
            'politiebureau'   => [ 5,  1,  0,  1,  2],
            'brandweerkazerne'=> [ 4,  1,  2,  1,  2],
            'park'            => [-2,  5,  4,  0,  0],
            'bioscoop'        => [-1,  4,  0,  2,  0],
            'sportpark'       => [ 0,  5,  2,  3,  0],
            'waterzuivering'  => [ 0,  0,  5,  2,  0],
            'school'          => [ 2,  2,  0,  5, -3],
            'winkel'          => [ 0,  0, -2,  5,  0],
            'ziekenhuis'      => [ 3,  0,  0,  5,  0],
            'station'         => [-2,  2,  0,  4,  5],
            'weg'             => [-4,  2, -4,  3,  5],
            'fietspad'        => [ 0,  3,  3,  3,  4],
            'tankstation'     => [-2,  0, -4,  1,  4],
        ];

        $categoryOrder = [
            'veiligheid',
            'recreatie',
            'milieukwaliteit',
            'voorzieningen',
            'mobiliteit',
        ];

        $rows = [];

        foreach ($matrix as $facilitySlug => $scores) {
            foreach ($categoryOrder as $index => $categorySlug) {
                $rows[] = [
                    'facility_id' => $facilities[$facilitySlug],
                    'category_id' => $categories[$categorySlug],
                    'score'       => $scores[$index],
                ];
            }
        }

        DB::table('facility_scores')->insert($rows);
    }
}