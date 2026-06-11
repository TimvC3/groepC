<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\RecurrenceType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('event_type');
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('recurrence_type', [RecurrenceType::None, RecurrenceType::Daily, RecurrenceType::Weekly, RecurrenceType::Monthly, RecurrenceType::Yearly])->default('none');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
