<?php

namespace App\Support;

class ZoningDesignationLibrary
{
    public static function all(): array
    {
        return [
            ['id' => 'police-station', 'name' => 'Police Station', 'category' => 'Security', 'icon' => "\u{1F693}"],
            ['id' => 'fire-station', 'name' => 'Fire Station', 'category' => 'Security', 'icon' => "\u{1F692}"],
            ['id' => 'park', 'name' => 'Park', 'category' => 'Recreation', 'icon' => "\u{1F333}"],
            ['id' => 'cinema', 'name' => 'Cinema', 'category' => 'Recreation', 'icon' => "\u{1F3AC}"],
            ['id' => 'sports-park', 'name' => 'Sports Park', 'category' => 'Recreation', 'icon' => "\u{26BD}"],
            ['id' => 'water-purification', 'name' => 'Water Purification', 'category' => 'Environmental Quality', 'icon' => "\u{1F4A7}"],
            ['id' => 'school', 'name' => 'School', 'category' => 'Facilities', 'icon' => "\u{1F3EB}"],
            ['id' => 'store', 'name' => 'Store', 'category' => 'Facilities', 'icon' => "\u{1F3EA}"],
            ['id' => 'hospital', 'name' => 'Hospital', 'category' => 'Facilities', 'icon' => "\u{1F3E5}"],
            ['id' => 'train-station', 'name' => 'Train Station', 'category' => 'Mobility', 'icon' => "\u{1F689}"],
            ['id' => 'road', 'name' => 'Road', 'category' => 'Mobility', 'icon' => "\u{1F6E3}\u{FE0F}"],
            ['id' => 'cycling-path', 'name' => 'Cycling Path', 'category' => 'Mobility', 'icon' => "\u{1F6B2}"],
            ['id' => 'petrol-station', 'name' => 'Petrol Station', 'category' => 'Mobility', 'icon' => "\u{26FD}"],
        ];
    }
}
