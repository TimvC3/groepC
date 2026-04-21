<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grid', function (Blueprint $table) {
            $table->id();
            $table->string('facility')->nullable();
            $table->integer('row');
            $table->Integer('collum');
            $table->integer('happyness');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grid');
    }
};