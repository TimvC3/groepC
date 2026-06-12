<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->foreignId('required_neighbour_facility_id')
                ->nullable()
                ->after('sort_order')
                ->constrained('facilities')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('required_neighbour_facility_id');
        });
    }
};