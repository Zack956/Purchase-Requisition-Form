<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-db', function() {
    try {
        DB::connection()->getPdo();
        return "SQLite connected successfully!";
    } catch (\Exception $e) {
        return "Connection failed: " . $e->getMessage();
    }
});

Route::get('/download-quotations/{pr}', \App\Http\Controllers\DownloadQuotationsController::class)
    ->name('filament.download-all');