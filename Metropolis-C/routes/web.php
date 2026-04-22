<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/grid', function () {
    return view('grid.grid');
});

Route::get('/grid-drag', function(){
    $images = ['image1.jpg', 'image2.jpg', 'image3.jpg', 'image4.jpg'];
    return view('grid.grid-drag', compact('images'));
});

require __DIR__.'/auth.php';
