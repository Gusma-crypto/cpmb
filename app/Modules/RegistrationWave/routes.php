<?php

use App\Modules\RegistrationWave\Controllers\RegistrationWaveController;
use Illuminate\Support\Facades\Route;

Route::resource('registration-waves', RegistrationWaveController::class)
    ->parameters(['registration-waves' => 'registrationWave'])
    ->except('show');
