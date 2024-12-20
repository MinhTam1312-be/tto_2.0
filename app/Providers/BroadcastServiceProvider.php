<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;


class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Broadcast::routes();
        // Log::info("BroadcastServiceProvider booting.");
        require base_path('routes/channels.php');
    }
}
