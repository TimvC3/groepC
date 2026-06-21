<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'city_planner', 'policy_maker', 'library_manager') NOT NULL DEFAULT 'city_planner'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('users')
            ->where('role', 'library_manager')
            ->update(['role' => 'city_planner']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'city_planner', 'policy_maker') NOT NULL DEFAULT 'city_planner'");
    }
};
