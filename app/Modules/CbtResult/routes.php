<?php

use App\Modules\CbtResult\Controllers\Admin\CbtResultController;
use Illuminate\Support\Facades\Route;

Route::prefix('cbt/results')->name('cbt.results.')->group(function (): void {
    Route::get('/', [CbtResultController::class, 'index'])->name('index');
    Route::post('/publish-many', [CbtResultController::class, 'publishMany'])->name('publish-many');
    Route::post('/{result}/publish', [CbtResultController::class, 'publish'])->name('publish');
    Route::post('/{result}/unpublish', [CbtResultController::class, 'unpublish'])->name('unpublish');
});
