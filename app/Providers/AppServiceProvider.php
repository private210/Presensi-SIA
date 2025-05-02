<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\DownloadTemplateController;

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
        // Register routes untuk download templates
        Route::get('/download/template/siswa', [TemplateController::class, 'downloadSiswaTemplate'])
            ->name('download.siswa.template');

        Route::get('/download/template/user', [DownloadTemplateController::class, 'downloadUserTemplate'])
            ->name('download.user.template');

        DB::listen(
            function ($query) {
                Log::info($query->sql, $query->bindings);
            }
        );
    }
}
