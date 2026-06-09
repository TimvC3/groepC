<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_conditions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('facility_id')
                ->constrained('facilities')
                ->cascadeOnDelete();

            $table->enum('condition_type', [
                'required_neighbour',
                'forbidden_neighbour',
            ]);

            $table->foreignId('neighbour_facility_id')
                ->constrained('facilities')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'facility_id',
                'condition_type',
                'neighbour_facility_id',
            ], 'unique_facility_condition');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_conditions');
    }
};