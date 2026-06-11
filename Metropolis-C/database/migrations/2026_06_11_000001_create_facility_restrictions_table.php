<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id_1')->constrained('facilities')->cascadeOnDelete();
            $table->foreignId('facility_id_2')->constrained('facilities')->cascadeOnDelete();
            $table->unique(['facility_id_1', 'facility_id_2']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_restrictions');
    }
};
