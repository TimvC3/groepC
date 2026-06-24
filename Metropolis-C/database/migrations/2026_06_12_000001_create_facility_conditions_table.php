<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('facility_conditions')) {
            Schema::create('facility_conditions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
                $table->enum('condition_type', ['required_neighbour', 'forbidden_neighbour']);
                $table->foreignId('neighbour_facility_id')->constrained('facilities')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(
                    ['facility_id', 'condition_type', 'neighbour_facility_id'],
                    'unique_facility_condition'
                );
            });
        }

        DB::table('facilities')
            ->whereNotNull('required_neighbour_facility_id')
            ->orderBy('id')
            ->each(function (object $facility): void {
                DB::table('facility_conditions')->updateOrInsert(
                    [
                        'facility_id' => $facility->id,
                        'condition_type' => 'required_neighbour',
                        'neighbour_facility_id' => $facility->required_neighbour_facility_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            });

        DB::table('facility_restrictions')
            ->orderBy('id')
            ->each(function (object $restriction): void {
                DB::table('facility_conditions')->updateOrInsert(
                    [
                        'facility_id' => $restriction->facility_id_1,
                        'condition_type' => 'forbidden_neighbour',
                        'neighbour_facility_id' => $restriction->facility_id_2,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_conditions');
    }
};
