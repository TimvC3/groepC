<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            'veiligheid' => ['name' => 'Security', 'slug' => 'security'],
            'recreatie' => ['name' => 'Recreation', 'slug' => 'recreation'],
            'milieukwaliteit' => ['name' => 'Environmental Quality', 'slug' => 'environmental-quality'],
            'voorzieningen' => ['name' => 'Facilities', 'slug' => 'facilities'],
            'mobiliteit' => ['name' => 'Mobility', 'slug' => 'mobility'],
        ];

        foreach ($categories as $oldSlug => $values) {
            DB::table('categories')
                ->where('slug', $oldSlug)
                ->update($values);
        }

        $facilities = [
            'politiebureau' => ['name' => 'Police Station', 'slug' => 'police-station'],
            'brandweerkazerne' => ['name' => 'Fire Station', 'slug' => 'fire-station'],
            'bioscoop' => ['name' => 'Cinema', 'slug' => 'cinema'],
            'sportpark' => ['name' => 'Sports Park', 'slug' => 'sports-park'],
            'waterzuivering' => ['name' => 'Water Purification', 'slug' => 'water-purification'],
            'winkel' => ['name' => 'Store', 'slug' => 'store'],
            'ziekenhuis' => ['name' => 'Hospital', 'slug' => 'hospital'],
            'station' => ['name' => 'Train Station', 'slug' => 'train-station'],
            'weg' => ['name' => 'Road', 'slug' => 'road'],
            'fietspad' => ['name' => 'Cycling Path', 'slug' => 'cycling-path'],
            'tankstation' => ['name' => 'Petrol Station', 'slug' => 'petrol-station'],
        ];

        foreach ($facilities as $oldSlug => $values) {
            DB::table('facilities')
                ->where('slug', $oldSlug)
                ->update($values);
        }
    }

    public function down(): void
    {
        $categories = [
            'security' => ['name' => 'Veiligheid', 'slug' => 'veiligheid'],
            'recreation' => ['name' => 'Recreatie', 'slug' => 'recreatie'],
            'environmental-quality' => ['name' => 'Milieukwaliteit', 'slug' => 'milieukwaliteit'],
            'facilities' => ['name' => 'Voorzieningen', 'slug' => 'voorzieningen'],
            'mobility' => ['name' => 'Mobiliteit', 'slug' => 'mobiliteit'],
        ];

        foreach ($categories as $oldSlug => $values) {
            DB::table('categories')
                ->where('slug', $oldSlug)
                ->update($values);
        }

        $facilities = [
            'police-station' => ['name' => 'Politiebureau', 'slug' => 'politiebureau'],
            'fire-station' => ['name' => 'Brandweerkazerne', 'slug' => 'brandweerkazerne'],
            'cinema' => ['name' => 'Bioscoop', 'slug' => 'bioscoop'],
            'sports-park' => ['name' => 'Sportpark', 'slug' => 'sportpark'],
            'water-purification' => ['name' => 'Waterzuivering', 'slug' => 'waterzuivering'],
            'store' => ['name' => 'Winkel', 'slug' => 'winkel'],
            'hospital' => ['name' => 'Ziekenhuis', 'slug' => 'ziekenhuis'],
            'train-station' => ['name' => 'Station', 'slug' => 'station'],
            'road' => ['name' => 'Weg', 'slug' => 'weg'],
            'cycling-path' => ['name' => 'Fietspad', 'slug' => 'fietspad'],
            'petrol-station' => ['name' => 'Tankstation', 'slug' => 'tankstation'],
        ];

        foreach ($facilities as $oldSlug => $values) {
            DB::table('facilities')
                ->where('slug', $oldSlug)
                ->update($values);
        }
    }
};
