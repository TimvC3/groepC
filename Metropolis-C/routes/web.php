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

    Route::get('/facilities', [FacilityController::class, 'index'])->name('functions.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'library-manager'])->group(function () {
    Route::post('/functions/{facility}/conditions', [FacilityConditionController::class, 'store'])
        ->name('functions.function.conditions.store');

    Route::patch('/functions/{facility}/conditions/{condition}', [FacilityConditionController::class, 'update'])
        ->name('functions.function.conditions.update');

    Route::delete('/functions/{facility}/conditions/{condition}', [FacilityConditionController::class, 'destroy'])
        ->name('functions.function.conditions.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/facilities', [FacilityController::class, 'store'])->name('functions.store');
    Route::get('/facilities/{facility}/edit', [FacilityController::class, 'edit'])->name('functions.edit');
    Route::patch('/facilities/{facility}', [FacilityController::class, 'updateFacility'])->name('functions.update');

    Route::patch('/functions/scores/{facilityScore}', [FacilityController::class, 'update'])
        ->name('functions.scores.update');

    Route::redirect('/functions', '/facilities');
});

Route::middleware(['auth', 'library-manager'])->group(function () {
    Route::post('/functions/conditions', [FacilityConditionController::class, 'store'])
        ->name('functions.conditions.store');
    Route::patch('/functions/conditions/{condition}', [FacilityConditionController::class, 'update'])
        ->name('functions.conditions.update');
    Route::delete('/functions/conditions/{condition}', [FacilityConditionController::class, 'destroy'])
        ->name('functions.conditions.destroy');
});

Route::middleware(['auth', 'library-manager'])
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
Route::post('/events/{event}/reschedule', [EventController::class, 'reschedule'])
    ->name('events.reschedule');