<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Modules\Biodata\Controllers\StudentBiodataController;
use App\Modules\Document\Controllers\DocumentController;
use App\Modules\ExamCard\Controllers\StudentExamCardController;
use App\Modules\ExamCard\Controllers\ExamCardPrintController;
use App\Modules\ExamRoom\Controllers\LecturerExamRoomController;
use App\Modules\ExamRoom\Controllers\StudentExamRoomController;
use App\Modules\ExamSchedule\Controllers\LecturerExamScheduleController;
use App\Modules\ExamSchedule\Controllers\StudentExamScheduleController;
use App\Modules\CbtResult\Controllers\Student\StudentCbtResultController;
use App\Modules\Registration\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('registrations', RegistrationController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::post('registrations/{registration}/submit', [RegistrationController::class, 'submit'])
        ->name('registrations.submit');
    Route::post('registrations/{registration}/verify', [RegistrationController::class, 'verify'])
        ->name('registrations.verify');
    Route::post('registrations/{registration}/review', [RegistrationController::class, 'startReview'])
        ->name('registrations.review');
    Route::post('registrations/{registration}/revision', [RegistrationController::class, 'requestRevision'])
        ->name('registrations.revision');
    Route::post('registrations/{registration}/reject', [RegistrationController::class, 'reject'])
        ->name('registrations.reject');

    Route::resource('biodata', StudentBiodataController::class)
        ->parameters(['biodata' => 'student_biodata']);

    Route::resource('documents', DocumentController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])
        ->name('documents.download');
    Route::get('documents/{document}/view', [DocumentController::class, 'view'])
        ->name('documents.view');
    Route::post('documents/{document}/approve', [DocumentController::class, 'approve'])
        ->name('documents.approve');
    Route::post('documents/{document}/reject', [DocumentController::class, 'reject'])
        ->name('documents.reject');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/registration', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/biodata', [StudentBiodataController::class, 'index'])->name('biodata.index');
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/exam-schedule', [StudentExamScheduleController::class, 'show'])->name('exam-schedule.show');
    Route::get('/exam-room', [StudentExamRoomController::class, 'show'])->name('exam-room.show');
    Route::get('/exam-card', [StudentExamCardController::class, 'index'])->name('exam-card.index');
    Route::post('/exam-card/{examCard}/print', [ExamCardPrintController::class, 'store'])->name('exam-card.print');
    Route::get('/cbt/results', [StudentCbtResultController::class, 'index'])->name('cbt.results.index');
    require app_path('Modules/CbtAttempt/routes.php');
});

Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/registrations/export/pdf', [RegistrationController::class, 'exportPdf'])->name('registrations.export.pdf');
        Route::get('/registrations/export/excel', [RegistrationController::class, 'exportExcel'])->name('registrations.export.excel');
        Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
        Route::get('/biodata', [StudentBiodataController::class, 'index'])->name('biodata.index');
        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::resource('users', UserController::class)->except('show');

        require app_path('Modules/Program/routes.php');
        require app_path('Modules/AcademicYear/routes.php');
        require app_path('Modules/RegistrationWave/routes.php');
        require app_path('Modules/ExamSchedule/routes.php');
        require app_path('Modules/ExamRoom/routes.php');
        require app_path('Modules/ExamCard/routes.php');
        require app_path('Modules/QuestionBank/routes.php');
        require app_path('Modules/CbtExam/routes.php');
        require app_path('Modules/CbtResult/routes.php');
    });
});

Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'staff'])->name('dashboard');
    Route::get('/registrations/export/pdf', [RegistrationController::class, 'exportPdf'])->name('registrations.export.pdf');
    Route::get('/registrations/export/excel', [RegistrationController::class, 'exportExcel'])->name('registrations.export.excel');
    Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/biodata', [StudentBiodataController::class, 'index'])->name('biodata.index');
    require app_path('Modules/ExamSchedule/routes.php');
    require app_path('Modules/ExamRoom/routes.php');
    require app_path('Modules/ExamCard/routes.php');
});

Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dosen'])->name('dashboard');
    Route::get('/participants', [DashboardController::class, 'dosen'])->name('participants.index');
    Route::get('/assessments', [DashboardController::class, 'dosen'])->name('assessments.index');
    Route::get('/exam-schedules', [LecturerExamScheduleController::class, 'index'])->name('exam-schedules.index');
    Route::get('/exam-rooms', [LecturerExamRoomController::class, 'index'])->name('exam-rooms.index');
});

require app_path('Modules/Payment/routes.php');

require __DIR__.'/auth.php';
