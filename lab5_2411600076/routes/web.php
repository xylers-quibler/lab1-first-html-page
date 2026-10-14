<?php

use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAdjustmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', function() { return 'Profile page placeholder'; })->name('profile.edit');
    Route::resource('products', ProductController::class);

    Route::resource('transactions', InventoryTransactionController::class)->only(['index', 'create', 'store', 'show']);
    Route::delete('/transactions/{transaction}', [InventoryTransactionController::class, 'destroy'])
        ->middleware('can:admin')->name('transactions.destroy');

    Route::middleware('can:admin')->group(function () {
        Route::get('/adjustments/create', [StockAdjustmentController::class, 'create'])->name('adjustments.create');
        Route::post('/adjustments', [StockAdjustmentController::class, 'store'])->name('adjustments.store');
    });

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');

    Route::prefix('api')->group(function () {
        Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);
        Route::get('/products/low-stock', [DashboardApiController::class, 'lowStock']);
        Route::get('/transactions/recent', [DashboardApiController::class, 'recentTransactions']);
        Route::get('/products/{product}/stock', [DashboardApiController::class, 'productStock']);
    });
});

require __DIR__.'/auth.php';
