<?php

use App\Modules\AcademicYear\Controllers\AcademicYearController;
use Illuminate\Support\Facades\Route;

Route::patch('academic-years/{academicYear}/deactivate', [AcademicYearController::class, 'deactivate'])
    ->name('academic-years.deactivate');

Route::resource('academic-years', AcademicYearController::class)
    ->parameters(['academic-years' => 'academicYear'])
    ->except('show');
