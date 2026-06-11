<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approved_grid_cells', function (Blueprint $table) {
            $table->unsignedTinyInteger('cell_index')->after('id');
            $table->string('item_type')->after('cell_index');
            $table->unsignedBigInteger('item_id')->after('item_type');
            $table->string('item_name')->after('item_id');

            $table->foreignId('approved_by')
                ->nullable()
                ->after('item_name')
                ->constrained('users')
                ->nullOnDelete();

            $table->unique('cell_index');
        });
    }

    public function down(): void
    {
        Schema::table('approved_grid_cells', function (Blueprint $table) {
            $table->dropUnique(['cell_index']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'cell_index',
                'item_type',
                'item_id',
                'item_name',
                'approved_by',
            ]);
        });
    }
};
