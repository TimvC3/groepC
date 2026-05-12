<?php

use App\Http\Controllers\ProfileController;
use App\Models\ZoningDesignation;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ZoningDesignationController;

Route::get('/', function () {
    return redirect("/grid");
});

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/grid', function () {
        $zoningDesignations = ZoningDesignation::orderBy('category')
            ->orderBy('name')
            ->get();

        return view('grid.grid', compact('zoningDesignations'));
    })->name('grid');

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