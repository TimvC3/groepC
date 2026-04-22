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
            ['slug' => 'police-station', 'name' => 'Police Station', 'category' => 'Safety', 'icon' => '🚓'],
            ['slug' => 'fire-station', 'name' => 'Fire Station', 'category' => 'Safety', 'icon' => '🚒'],
            ['slug' => 'park', 'name' => 'Park', 'category' => 'Recreation', 'icon' => '🌳'],
            ['slug' => 'cinema', 'name' => 'Cinema', 'category' => 'Recreation', 'icon' => '🎬'],
            ['slug' => 'sports-park', 'name' => 'Sports Park', 'category' => 'Recreation', 'icon' => '⚽'],
            ['slug' => 'water-purification', 'name' => 'Water Purification', 'category' => 'Environment', 'icon' => '💧'],
            ['slug' => 'primary-school', 'name' => 'Primary School', 'category' => 'Facility', 'icon' => '🏫'],
            ['slug' => 'store', 'name' => 'Store', 'category' => 'Facility', 'icon' => '🏪'],
            ['slug' => 'hospital', 'name' => 'Hospital', 'category' => 'Facility', 'icon' => '🏥'],
            ['slug' => 'train-station', 'name' => 'Train Station', 'category' => 'Mobility', 'icon' => '🚉'],
            ['slug' => 'road', 'name' => 'Road', 'category' => 'Mobility', 'icon' => '🛣️'],
            ['slug' => 'cycling-path', 'name' => 'Cycling Path', 'category' => 'Mobility', 'icon' => '🚲'],
            ['slug' => 'petrol-station', 'name' => 'Petrol Station', 'category' => 'Mobility', 'icon' => '⛽'],
        ];

        foreach ($designations as $designation) {
            ZoningDesignation::updateOrCreate(
                ['slug' => $designation['slug']],
                $designation
            );
        }
    }
}