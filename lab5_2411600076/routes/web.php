<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', function() { return 'Profile page placeholder'; })->name('profile.edit');
    Route::resource('products', ProductController::class);
});

require __DIR__.'/auth.php';