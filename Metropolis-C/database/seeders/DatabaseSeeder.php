<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

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
            'role' => 'city_planner',
        ]);
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'Password',
            'role' => 'admin',
        ]);
        User::factory()->create([
            'name' => 'Library Manager',
            'email' => 'library.manager@example.com',
            'password' => 'Password',
            'role' => 'library_manager',
        ]);
        User::factory()->create([
            'name' => 'Policy Maker',
            'email' => 'policy.maker@example.com',
            'password' => 'Password',
            'role' => 'policy_maker',
        ]);
        User::factory()->create([
            'name' => 'Library Manager',
            'email' => 'library.manager@example.com',
            'password' => 'Password',
            'role' => 'library_manager',
        ]);
        $this->call([
            CategorySeeder::class,
            EventSeeder::class,
            FacilitySeeder::class,
            FacilityScoreSeeder::class,
            ZoningDesignationSeeder::class,
        ]);

    }
}
