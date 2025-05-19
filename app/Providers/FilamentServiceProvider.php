<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Set import untuk berjalan synchronously (tanpa queue)
        // config(['filament-actions.imports.is_queued' => false]);
    }
}
