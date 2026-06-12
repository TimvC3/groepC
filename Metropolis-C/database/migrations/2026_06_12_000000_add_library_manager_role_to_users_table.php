<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE users MODIFY role ENUM('admin', 'library_manager', 'city_planner', 'policy_maker') NOT NULL DEFAULT 'city_planner'"
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE users MODIFY role ENUM('admin', 'city_planner', 'policy_maker') NOT NULL DEFAULT 'city_planner'"
            );
        }
    }
};
