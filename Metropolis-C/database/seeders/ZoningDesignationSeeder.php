<?php

namespace Database\Seeders;

use App\Models\ZoningDesignation;
use Illuminate\Database\Seeder;

class ZoningDesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            ['slug' => 'police-station', 'name' => 'Police Station', 'category' => 'Security', 'icon' => "\u{1F693}"],
            ['slug' => 'fire-station', 'name' => 'Fire Station', 'category' => 'Security', 'icon' => "\u{1F692}"],
            ['slug' => 'park', 'name' => 'Park', 'category' => 'Recreation', 'icon' => "\u{1F333}"],
            ['slug' => 'cinema', 'name' => 'Cinema', 'category' => 'Recreation', 'icon' => "\u{1F3AC}"],
            ['slug' => 'sports-park', 'name' => 'Sports Park', 'category' => 'Recreation', 'icon' => "\u{26BD}"],
            ['slug' => 'water-purification', 'name' => 'Water Purification', 'category' => 'Environmental Quality', 'icon' => "\u{1F4A7}"],
            ['slug' => 'school', 'name' => 'School', 'category' => 'Facilities', 'icon' => "\u{1F3EB}"],
            ['slug' => 'store', 'name' => 'Store', 'category' => 'Facilities', 'icon' => "\u{1F3EA}"],
            ['slug' => 'hospital', 'name' => 'Hospital', 'category' => 'Facilities', 'icon' => "\u{1F3E5}"],
            ['slug' => 'train-station', 'name' => 'Train Station', 'category' => 'Mobility', 'icon' => "\u{1F689}"],
            ['slug' => 'road', 'name' => 'Road', 'category' => 'Mobility', 'icon' => "\u{1F6E3}\u{FE0F}"],
            ['slug' => 'cycling-path', 'name' => 'Cycling Path', 'category' => 'Mobility', 'icon' => "\u{1F6B2}"],
            ['slug' => 'petrol-station', 'name' => 'Petrol Station', 'category' => 'Mobility', 'icon' => "\u{26FD}"],
        ];

        foreach ($designations as $designation) {
            ZoningDesignation::updateOrCreate(
                ['slug' => $designation['slug']],
                $designation
            );
        }
    }
}
