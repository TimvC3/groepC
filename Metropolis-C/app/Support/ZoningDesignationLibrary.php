<?php

namespace App\Support;

class ZoningDesignationLibrary
{
    public static function all(): array
    {
        return [
            ['id' => 'police-station', 'name' => 'Police Station', 'category' => 'Safety', 'icon' => '🚓'],
            ['id' => 'fire-station', 'name' => 'Fire Station', 'category' => 'Safety', 'icon' => '🚒'],
            ['id' => 'park', 'name' => 'Park', 'category' => 'Recreation', 'icon' => '🌳'],
            ['id' => 'cinema', 'name' => 'Cinema', 'category' => 'Recreation', 'icon' => '🎬'],
            ['id' => 'sports-park', 'name' => 'Sports Park', 'category' => 'Recreation', 'icon' => '⚽'],
            ['id' => 'water-purification', 'name' => 'Water Purification', 'category' => 'Environment', 'icon' => '💧'],
            ['id' => 'primary-school', 'name' => 'Primary School', 'category' => 'Facility', 'icon' => '🏫'],
            ['id' => 'store', 'name' => 'Store', 'category' => 'Facility', 'icon' => '🏪'],
            ['id' => 'hospital', 'name' => 'Hospital', 'category' => 'Facility', 'icon' => '🏥'],
            ['id' => 'train-station', 'name' => 'Train Station', 'category' => 'Mobility', 'icon' => '🚉'],
            ['id' => 'road', 'name' => 'Road', 'category' => 'Mobility', 'icon' => '🛣️'],
            ['id' => 'cycling-path', 'name' => 'Cycling Path', 'category' => 'Mobility', 'icon' => '🚲'],
            ['id' => 'petrol-station', 'name' => 'Petrol Station', 'category' => 'Mobility', 'icon' => '⛽'],
        ];
    }
}