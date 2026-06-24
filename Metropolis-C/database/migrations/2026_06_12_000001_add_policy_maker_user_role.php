<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'city_planner', 'policy_maker', 'library_manager'])
                ->default('city_planner')
                ->change();
        });
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET role = 'city_planner' WHERE role NOT IN ('admin', 'city_planner')");

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'city_planner'])
                ->default('city_planner')
                ->change();
        });
    }
};