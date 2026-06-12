<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\FacilityConditionController;
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
    Route::post('/grid/approve-cell', [GridController::class, 'approveCell'])->name('grid.approve-cell');

    Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'library-manager'])->group(function () {
    Route::post('/facilities/{facility}/conditions', [FacilityConditionController::class, 'store'])
        ->name('facilities.conditions.store');

    Route::patch('/facilities/{facility}/conditions/{condition}', [FacilityConditionController::class, 'update'])
        ->name('facilities.conditions.update');

    Route::delete('/facilities/{facility}/conditions/{condition}', [FacilityConditionController::class, 'destroy'])
        ->name('facilities.conditions.destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/facilities', [FacilityController::class, 'store'])->name('facilities.store');
    Route::get('/facilities/{facility}/edit', [FacilityController::class, 'edit'])->name('facilities.edit');
    Route::patch('/facilities/{facility}', [FacilityController::class, 'updateFacility'])->name('facilities.update');

    Route::patch('/facilities/scores/{facilityScore}', [FacilityController::class, 'update'])
        ->name('facilities.scores.update');

    Route::redirect('/functions', '/facilities')->name('functions.index');
});

Route::middleware(['auth', 'library-manager'])->group(function () {
    Route::post('/facility-conditions', [FacilityConditionController::class, 'store'])
        ->name('facility-conditions.store');
    Route::patch('/facility-conditions/{condition}', [FacilityConditionController::class, 'update'])
        ->name('facility-conditions.update');
    Route::delete('/facility-conditions/{condition}', [FacilityConditionController::class, 'destroy'])
        ->name('facility-conditions.destroy');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::redirect('/functions', '/facilities')->name('functions.index');
        Route::redirect('/functions/create', '/facilities')->name('functions.create');
        Route::post('/functions', [FacilityController::class, 'store'])->name('functions.store');
        Route::get('/functions/{facility}/edit', [FacilityController::class, 'edit'])->name('functions.edit');
        Route::patch('/functions/{facility}', [FacilityController::class, 'updateFacility'])->name('functions.update');
    });

Route::middleware(['auth', 'city-planner'])
    ->prefix('events')
    ->name('events.')
    ->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::post('/', [EventController::class, 'store'])->name('store');
        Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
        Route::patch('/{event}', [EventController::class, 'update'])->name('update');
    });
