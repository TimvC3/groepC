<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Don't forget this import!

class gridSeeder extends Seeder
{
    public function run(): void
    {
        for ($row = 1; $row <= 3; $row++) {
            for ($col = 1; $col <= 4; $col++) {
                DB::table('grid')->insert([
                    'facility' => null,
                    'row' => $row,
                    'collum' => $col, 
                    'happyness' => 0,
                ]);
            }
        }
    }
}