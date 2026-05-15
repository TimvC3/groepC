<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        // Retrieve category IDs by slug for clean referencing
        $categories = DB::table('categories')->pluck('id', 'slug');

        $facilities = [
            // --- Veiligheid ---
            [
                'category_id' => $categories['veiligheid'],
                'name'        => 'Politiebureau',
                'slug'        => 'politiebureau',
                'icon'        => '🚓',
                'sort_order'  => 1,
            ],
            [
                'category_id' => $categories['veiligheid'],
                'name'        => 'Brandweerkazerne',
                'slug'        => 'brandweerkazerne',
                'icon'        => '🚒',
                'sort_order'  => 2,
            ],

            // --- Recreatie ---
            [
                'category_id' => $categories['recreatie'],
                'name'        => 'Park',
                'slug'        => 'park',
                'icon'        => '🌳',
                'sort_order'  => 3,
            ],
            [
                'category_id' => $categories['recreatie'],
                'name'        => 'Bioscoop',
                'slug'        => 'bioscoop',
                'icon'        => '🎬',
                'sort_order'  => 4,
            ],
            [
                'category_id' => $categories['recreatie'],
                'name'        => 'Sportpark',
                'slug'        => 'sportpark',
                'icon'        => '⚽',
                'sort_order'  => 5,
            ],

            // --- Milieukwaliteit ---
            [
                'category_id' => $categories['milieukwaliteit'],
                'name'        => 'Waterzuivering',
                'slug'        => 'waterzuivering',
                'icon'        => '💧',
                'sort_order'  => 6,
            ],

            // --- Voorzieningen ---
            [
                'category_id' => $categories['voorzieningen'],
                'name'        => 'School',
                'slug'        => 'school',
                'icon'        => '🏫',
                'sort_order'  => 7,
            ],
            [
                'category_id' => $categories['voorzieningen'],
                'name'        => 'Winkel',
                'slug'        => 'winkel',
                'icon'        => '🏪',
                'sort_order'  => 8,
            ],
            [
                'category_id' => $categories['voorzieningen'],
                'name'        => 'Ziekenhuis',
                'slug'        => 'ziekenhuis',
                'icon'        => '🏥',
                'sort_order'  => 9,
            ],

            // --- Mobiliteit ---
            [
                'category_id' => $categories['mobiliteit'],
                'name'        => 'Station',
                'slug'        => 'station',
                'icon'        => '🚉',
                'sort_order'  => 10,
            ],
            [
                'category_id' => $categories['mobiliteit'],
                'name'        => 'Weg',
                'slug'        => 'weg',
                'icon'        => '🛣️',
                'sort_order'  => 11,
            ],
            [
                'category_id' => $categories['mobiliteit'],
                'name'        => 'Fietspad',
                'slug'        => 'fietspad',
                'icon'        => '🚲',
                'sort_order'  => 12,
            ],
            [
                'category_id' => $categories['mobiliteit'],
                'name'        => 'Tankstation',
                'slug'        => 'tankstation',
                'icon'        => '⛽',
                'sort_order'  => 13,
            ],
        ];

        DB::table('facilities')->insert($facilities);
    }
}