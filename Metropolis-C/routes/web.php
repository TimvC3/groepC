<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// auth routes (login, register, etc.)
require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {

    // Dashboard (laat staan)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ✅ GRID (FIXED)
    Route::get('/grid', function () {
        return view('grid.grid');
    })->name('grid');

    // Profile routes (nodig voor navbar)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});