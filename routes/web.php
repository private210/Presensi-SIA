<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\DownloadTemplateController;

Route::get('/', function () {
    return redirect('/admin/login');
});
Route::get('/download/template/siswa', [TemplateController::class, 'downloadSiswaTemplate'])->name('download.siswa.template');
Route::get('/download-user-template', [DownloadTemplateController::class, 'downloadUserTemplate'])
    ->name('download.user.template')
    ->middleware(['auth']);
