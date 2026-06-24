<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('facility_conditions')) {
            return;
        }

        Schema::table('facility_conditions', function (Blueprint $table): void {
            $table->enum('condition_type', [
                'required_neighbour',
                'forbidden_neighbour',
                'level_4_adjacency',
            ])->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('facility_conditions')) {
            return;
        }

        Schema::table('facility_conditions', function (Blueprint $table): void {
            $table->enum('condition_type', [
                'required_neighbour',
                'forbidden_neighbour',
            ])->change();
        });
    }
};
