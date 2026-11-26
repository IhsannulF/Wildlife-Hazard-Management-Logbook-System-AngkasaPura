<?php

namespace App\Providers;

use Illuminate\Foundation\DevCommands;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // `composer run dev` ikut menjalankan scheduler agar snapshot cuaca 15 menit (FR-0.5) terisi saat pengembangan.
        DevCommands::artisan('schedule:work', 'scheduler');
    }
}
