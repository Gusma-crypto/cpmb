<?php

use App\Modules\Program\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

Route::resource('programs', ProgramController::class)->except('show');
