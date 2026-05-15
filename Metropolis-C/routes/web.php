<?php

use App\Http\Controllers\Admin\ZoningDesignationController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\GridController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/grid');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/grid', [GridController::class, 'index'])->name('grid');
    Route::redirect('/dashboard', '/grid')->name('dashboard');

    Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities');
    Route::patch('/facilities/scores/{facilityScore}', [FacilityController::class, 'update'])->name('facilities.scores.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/functions', function () {
    return redirect()->route('admin.functions.index');
});

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('functions', ZoningDesignationController::class)
            ->only(['index', 'edit', 'update']);
    });
