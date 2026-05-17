<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Security',              'slug' => 'security',              'sort_order' => 1],
            ['name' => 'Recreation',            'slug' => 'recreation',            'sort_order' => 2],
            ['name' => 'Environmental Quality', 'slug' => 'environmental-quality', 'sort_order' => 3],
            ['name' => 'Facilities',            'slug' => 'facilities',            'sort_order' => 4],
            ['name' => 'Mobility',              'slug' => 'mobility',              'sort_order' => 5],
        ];

        DB::table('categories')->insert($categories);
    }
}
