<?php

namespace App\Providers;

use App\Models\InventoryTransaction;
use App\Models\User;
use App\Observers\InventoryTransactionObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        InventoryTransaction::observe(InventoryTransactionObserver::class);

        Paginator::useBootstrapFive();

        Gate::define('admin', function (User $user) {
            return $user->role === 'admin';
        });
    }
}
