<?php

use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\CodeController;
use App\Http\Controllers\Api\Admin\ExportController;
use App\Http\Controllers\Api\Admin\ParticipantController as AdminParticipantController;
use App\Http\Controllers\Api\Participant\AssessmentController;
use App\Http\Controllers\Api\Participant\AnswerController;
use App\Http\Controllers\Api\Participant\CodeValidationController;
use Illuminate\Support\Facades\Route;

Route::post('/code/validate', [CodeValidationController::class, 'store'])
    ->name('api.code.validate');

Route::post('/assessment/start', [AssessmentController::class, 'start'])
    ->name('api.assessment.start');
Route::get('/assessment/current', [AssessmentController::class, 'current'])
    ->name('api.assessment.current');
Route::post('/assessment/submit', [AssessmentController::class, 'submit'])
    ->name('api.assessment.submit');
Route::get('/assessment/completion', [AssessmentController::class, 'completion'])
    ->name('api.assessment.completion');

Route::put('/answers/autosave', [AnswerController::class, 'autosave'])
    ->middleware('web')
    ->name('api.answers.autosave');

Route::prefix('admin')->name('api.admin.')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::post('/codes', [CodeController::class, 'store'])->name('codes.store');
    Route::get('/codes', [CodeController::class, 'index'])->name('codes.index');
    Route::patch('/codes/{id}/reset', [CodeController::class, 'reset'])->name('codes.reset');
    Route::patch('/codes/{id}/lock', [CodeController::class, 'lock'])->name('codes.lock');
    Route::patch('/codes/{id}/unlock', [CodeController::class, 'unlock'])->name('codes.unlock');

    Route::get('/participants', [AdminParticipantController::class, 'index'])->name('participants.index');
    Route::get('/participants/{id}/result', [AdminParticipantController::class, 'result'])->name('participants.result');
    Route::post('/participants/{id}/notes', [AdminParticipantController::class, 'storeNote'])->name('participants.notes.store');
    Route::patch('/participants/{id}/final-status', [AdminParticipantController::class, 'updateFinalStatus'])->name('participants.final-status.update');

    Route::get('/export/results.csv', [ExportController::class, 'results'])->name('export.results');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
