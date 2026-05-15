<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Veiligheid',     'slug' => 'veiligheid',     'sort_order' => 1],
            ['name' => 'Recreatie',      'slug' => 'recreatie',      'sort_order' => 2],
            ['name' => 'Milieukwaliteit','slug' => 'milieukwaliteit','sort_order' => 3],
            ['name' => 'Voorzieningen',  'slug' => 'voorzieningen',  'sort_order' => 4],
            ['name' => 'Mobiliteit',     'slug' => 'mobiliteit',     'sort_order' => 5],
        ];

        DB::table('categories')->insert($categories);
    }
}