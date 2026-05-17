<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * facility_scores stores the impact score of a facility (row)
     * on a category (column), e.g. Police Station -> Security = 5.
     *
     * Score range: -5 (very negative) to 5 (very positive).
     */
    public function up(): void
    {
        Schema::create('facility_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('score'); // -5 to 5
            $table->timestamps();

            // Each facility has exactly one score per category
            $table->unique(['facility_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_scores');
    }
};
