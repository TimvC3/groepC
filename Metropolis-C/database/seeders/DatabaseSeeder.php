<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'City Planner',
            'email' => 'city.planner@example.com',
            'password' => 'Password',
        ]);
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'Password',
        ]);
        $this->call([
            CategorySeeder::class,
            FacilitySeeder::class,
            FacilityScoreSeeder::class,
            ZoningDesignationSeeder::class,
        ]);

        
    }
}