<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        // Retrieve category IDs by slug for clean referencing.
        $categories = DB::table('categories')->pluck('id', 'slug');

        $facilities = [
            // Security
            [
                'category_id' => $categories['security'],
                'name' => 'Police Station',
                'slug' => 'police-station',
                'icon' => "\u{1F693}",
                'sort_order' => 1,
            ],
            [
                'category_id' => $categories['security'],
                'name' => 'Fire Station',
                'slug' => 'fire-station',
                'icon' => "\u{1F692}",
                'sort_order' => 2,
            ],

            // Recreation
            [
                'category_id' => $categories['recreation'],
                'name' => 'Park',
                'slug' => 'park',
                'icon' => "\u{1F333}",
                'sort_order' => 3,
            ],
            [
                'category_id' => $categories['recreation'],
                'name' => 'Cinema',
                'slug' => 'cinema',
                'icon' => "\u{1F3AC}",
                'sort_order' => 4,
            ],
            [
                'category_id' => $categories['recreation'],
                'name' => 'Sports Park',
                'slug' => 'sports-park',
                'icon' => "\u{26BD}",
                'sort_order' => 5,
            ],

            // Environmental Quality
            [
                'category_id' => $categories['environmental-quality'],
                'name' => 'Water Purification',
                'slug' => 'water-purification',
                'icon' => "\u{1F4A7}",
                'sort_order' => 6,
            ],

            // Facilities
            [
                'category_id' => $categories['facilities'],
                'name' => 'School',
                'slug' => 'school',
                'icon' => "\u{1F3EB}",
                'sort_order' => 7,
            ],
            [
                'category_id' => $categories['facilities'],
                'name' => 'Store',
                'slug' => 'store',
                'icon' => "\u{1F3EA}",
                'sort_order' => 8,
            ],
            [
                'category_id' => $categories['facilities'],
                'name' => 'Hospital',
                'slug' => 'hospital',
                'icon' => "\u{1F3E5}",
                'sort_order' => 9,
            ],

            // Mobility
            [
                'category_id' => $categories['mobility'],
                'name' => 'Train Station',
                'slug' => 'train-station',
                'icon' => "\u{1F689}",
                'sort_order' => 10,
            ],
            [
                'category_id' => $categories['mobility'],
                'name' => 'Road',
                'slug' => 'road',
                'icon' => "\u{1F6E3}\u{FE0F}",
                'sort_order' => 11,
            ],
            [
                'category_id' => $categories['mobility'],
                'name' => 'Cycling Path',
                'slug' => 'cycling-path',
                'icon' => "\u{1F6B2}",
                'sort_order' => 12,
            ],
            [
                'category_id' => $categories['mobility'],
                'name' => 'Petrol Station',
                'slug' => 'petrol-station',
                'icon' => "\u{26FD}",
                'sort_order' => 13,
            ],
        ];

        DB::table('facilities')->insert($facilities);
    }
}
